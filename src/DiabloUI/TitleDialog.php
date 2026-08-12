<?php

declare(strict_types=1);

namespace Diablo\DiabloUI;

use Diablo\Engine\Assets\AssetStore;
use Diablo\Engine\Assets\Pcx;
use Diablo\Engine\Assets\UiFont;
use Diablo\Engine\Render\IsoCoords;
use Diablo\Platform\Sdl;

/**
 * DiabloUI title screen — UiTitleDialog (DevilutionX Source/DiabloUI/title.cpp).
 *
 * Assets: ui_art\title (BG) + ui_art\logo ×15 (chroma 250).
 * Frame clock: GetAnimationFrame → (elapsedMs / 60) % 15.
 * Dismiss: any key / mouse click / 7000 ms timeout.
 */
final class TitleDialog
{
    public const LOGO_FRAMES = 15;
    public const LOGO_CHROMA = 250;
    /** DevilutionX GetAnimationFrame divisor (ms per frame despite param name fps). */
    public const FRAME_MS = 60;
    public const TIMEOUT_MS = 7000;
    /** Logo Y offset from UI top (title.cpp: uiPosition.y + 182). */
    public const LOGO_Y = 182;
    /** Copyright band top (title.cpp: uiPosition.y + 410). */
    public const COPYRIGHT_Y = 410;
    public const COPYRIGHT_H = 26;
    /** ASCII (c) — classic artfont has no UTF-8 © glyph (UTF-8 would show as Â©). */
    public const COPYRIGHT_TEXT = 'Copyright (c) 1996-2001 Blizzard Entertainment';

    private string $bgRgba = '';
    private int $bgW = 0;
    private int $bgH = 0;
    /** @var list<string> */
    private array $logoFrames = [];
    private int $logoW = 0;
    private int $logoH = 0;
    private int $numFrames = self::LOGO_FRAMES;

    private ?object $bgTex = null;
    /** @var array<int, object> */
    private array $logoTex = [];
    private ?object $copyrightTex = null;
    private int $copyrightW = 0;
    private int $copyrightH = 0;

    private bool $loaded = false;
    private bool $promptVisible = false;

    public function load(AssetStore $assets): void
    {
        $titlePath = 'ui_art\\title.pcx';
        if (!$assets->has($titlePath)) {
            throw new \RuntimeException("TitleDialog: missing {$titlePath}");
        }
        $title = Pcx::decode($assets->read($titlePath));
        $this->bgRgba = $title['rgba'];
        $this->bgW = $title['w'];
        $this->bgH = $title['h'];

        $logoPath = 'ui_art\\logo.pcx';
        if (!$assets->has($logoPath)) {
            throw new \RuntimeException("TitleDialog: missing {$logoPath}");
        }
        $logo = Pcx::decodeSpriteList($assets->read($logoPath), self::LOGO_FRAMES, [self::LOGO_CHROMA]);
        $this->logoFrames = $logo['frames'];
        $this->logoW = $logo['w'];
        $this->logoH = $logo['frameH'];
        $this->numFrames = $logo['numFrames'];

        $this->copyrightRgba = '';
        $this->copyrightW = 0;
        $this->copyrightH = 0;
        try {
            $font = UiFont::load24($assets, 's');
            $img = $font->render(self::COPYRIGHT_TEXT, 2);
            $this->copyrightRgba = $img['rgba'];
            $this->copyrightW = $img['w'];
            $this->copyrightH = $img['h'];
        } catch (\Throwable $e) {
            fwrite(STDERR, 'TitleDialog copyright font: ' . $e->getMessage() . "\n");
        }

        $this->promptVisible = $this->copyrightRgba !== '';
        $this->loaded = true;
        $this->bgTex = null;
        $this->logoTex = [];
        $this->copyrightTex = null;
    }

    private string $copyrightRgba = '';

    public function isLoaded(): bool
    {
        return $this->loaded;
    }

    public function numFrames(): int
    {
        return $this->numFrames;
    }

    public function logoSize(): array
    {
        return ['w' => $this->logoW, 'h' => $this->logoH];
    }

    public function bgSize(): array
    {
        return ['w' => $this->bgW, 'h' => $this->bgH];
    }

    /** GetAnimationFrame(frames) with fps=60 → ms divisor 60. */
    public function frameAt(int $elapsedMs): int
    {
        $n = max(1, $this->numFrames);

        return intdiv(max(0, $elapsedMs), self::FRAME_MS) % $n;
    }

    public function shouldDismiss(int $elapsedMs, bool $anyKey, bool $mouseClick): bool
    {
        if ($elapsedMs >= self::TIMEOUT_MS) {
            return true;
        }

        return $anyKey || $mouseClick;
    }

    public function promptVisible(): bool
    {
        return $this->promptVisible;
    }

    /**
     * Upload BG + all logo frames + copyright once (warm-up).
     */
    public function ensureTextures(Sdl $sdl): void
    {
        if (!$this->loaded) {
            return;
        }
        if ($this->bgTex === null && $this->bgRgba !== '') {
            $this->bgTex = $sdl->createTextureRGBA($this->bgW, $this->bgH, $this->bgRgba);
        }
        for ($i = 0; $i < $this->numFrames; $i++) {
            if (!isset($this->logoTex[$i]) && isset($this->logoFrames[$i])) {
                $this->logoTex[$i] = $sdl->createTextureRGBA(
                    $this->logoW,
                    $this->logoH,
                    $this->logoFrames[$i]
                );
            }
        }
        if ($this->copyrightTex === null && $this->copyrightRgba !== '') {
            $this->copyrightTex = $sdl->createTextureRGBA(
                $this->copyrightW,
                $this->copyrightH,
                $this->copyrightRgba
            );
        }
    }

    /**
     * Draw order: background → logo @ y+182 centered → copyright @ y+410.
     *
     * @param int|null $forceFrame freeze to this frame index (debug-title-frame)
     */
    public function render(Sdl $sdl, int $uiX, int $uiY, int $elapsedMs, ?int $forceFrame = null): int
    {
        $this->ensureTextures($sdl);
        $screenW = IsoCoords::SCREEN_W;
        $screenH = IsoCoords::SCREEN_H;

        if ($this->bgTex !== null) {
            $dx = $uiX + intdiv($screenW - $this->bgW, 2);
            $dy = $uiY + intdiv($screenH - $this->bgH, 2);
            $sdl->copyTexture($this->bgTex, $dx, $dy, $this->bgW, $this->bgH);
        }

        $fi = $forceFrame !== null
            ? max(0, min($this->numFrames - 1, $forceFrame))
            : $this->frameAt($elapsedMs);
        if (isset($this->logoTex[$fi])) {
            $lx = $uiX + intdiv($screenW - $this->logoW, 2);
            $ly = $uiY + self::LOGO_Y;
            $sdl->copyTexture($this->logoTex[$fi], $lx, $ly, $this->logoW, $this->logoH);
        }

        if ($this->copyrightTex !== null) {
            $cx = $uiX + intdiv($screenW - $this->copyrightW, 2);
            $cy = $uiY + self::COPYRIGHT_Y + intdiv(self::COPYRIGHT_H - $this->copyrightH, 2);
            $sdl->copyTexture($this->copyrightTex, $cx, $cy, $this->copyrightW, $this->copyrightH);
        }

        return $fi;
    }

    /**
     * Diagnostic dump for --debug-title-assets.
     *
     * @return list<array<string,mixed>>
     */
    public static function probeAssets(AssetStore $assets): array
    {
        $rows = [];
        foreach (['ui_art\\title.pcx', 'ui_art\\logo.pcx', 'ui_art\\font24s.pcx', 'ui_art\\font24.bin'] as $path) {
            $row = [
                'path' => $path,
                'found' => $assets->has($path),
                'format' => str_ends_with(strtolower($path), '.pcx') ? 'pcx' : 'bin',
                'size' => 0,
                'frame_count' => null,
                'dimensions' => null,
            ];
            if ($row['found']) {
                $bytes = $assets->read($path);
                $row['size'] = strlen($bytes);
                if (str_ends_with(strtolower($path), '.pcx')) {
                    try {
                        if ($path === 'ui_art\\logo.pcx') {
                            $list = Pcx::decodeSpriteList($bytes, self::LOGO_FRAMES, [self::LOGO_CHROMA]);
                            $row['frame_count'] = $list['numFrames'];
                            $row['dimensions'] = sprintf(
                                '%dx%d sheet; frame %dx%d',
                                $list['w'],
                                $list['sheetH'],
                                $list['w'],
                                $list['frameH']
                            );
                        } else {
                            $dec = Pcx::decode($bytes);
                            $row['frame_count'] = 1;
                            $row['dimensions'] = $dec['w'] . 'x' . $dec['h'];
                        }
                    } catch (\Throwable $e) {
                        $row['dimensions'] = 'decode error: ' . $e->getMessage();
                    }
                }
            }
            $rows[] = $row;
        }

        return $rows;
    }
}
