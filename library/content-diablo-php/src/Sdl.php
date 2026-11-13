<?php

declare(strict_types=1);

namespace Diablo;

use FFI;
use FFI\CData;

/**
 * Thin SDL2 FFI boundary. All native window/render/input access stays here.
 */
final class Sdl
{
    private const SDL_INIT_VIDEO = 0x00000020;
    private const SDL_WINDOWPOS_CENTERED = 0x1FFF0000;
    private const SDL_QUIT = 0x100;
    private const SDL_KEYDOWN = 0x300;
    private const SDL_MOUSEBUTTONDOWN = 0x401;
    private const SDLK_ESCAPE = 27;
    private const SDLK_RETURN = 13;
    private const SDLK_SPACE = 32;
    private const SDL_BUTTON_LEFT = 1;
    private const SDL_BUTTON_RIGHT = 3;

    private FFI $ffi;
    private ?CData $window = null;
    private ?CData $renderer = null;
    private CData $event;
    private CData $rect;

    public function __construct()
    {
        if (!extension_loaded('ffi')) {
            $this->fail('ext-ffi is not loaded. Run with: php -d ffi.enable=true bin/game.php');
        }

        if (ini_get('ffi.enable') === '0' || ini_get('ffi.enable') === 'false') {
            $this->fail('FFI is disabled. Run with: php -d ffi.enable=true bin/game.php');
        }

        $library = $this->resolveLibrary();

        try {
            $this->ffi = FFI::cdef($this->cdef(), $library);
        } catch (\Throwable $e) {
            $this->fail('Failed to load SDL2 via FFI (' . $library . '): ' . $e->getMessage());
        }

        $this->event = $this->ffi->new('SDL_Event');
        $this->rect = $this->ffi->new('SDL_Rect');
    }

    public function init(string $title, int $width, int $height): void
    {
        if ($this->ffi->SDL_Init(self::SDL_INIT_VIDEO) !== 0) {
            $this->fail('SDL_Init failed: ' . $this->error());
        }

        $this->window = $this->ffi->SDL_CreateWindow(
            $title,
            self::SDL_WINDOWPOS_CENTERED,
            self::SDL_WINDOWPOS_CENTERED,
            $width,
            $height,
            0
        );

        if ($this->window === null) {
            $this->fail('SDL_CreateWindow failed: ' . $this->error());
        }

        $this->renderer = $this->ffi->SDL_CreateRenderer($this->window, -1, 0);
        if ($this->renderer === null) {
            $this->fail('SDL_CreateRenderer failed: ' . $this->error());
        }
    }

    /**
     * @return array{
     *   quit: bool,
     *   escape: bool,
     *   enter: bool,
     *   keys: list<string>,
     *   click: ?array{x: int, y: int, button: int}
     * }
     */
    public function pollEvents(): array
    {
        $quit = false;
        $escape = false;
        $enter = false;
        $keys = [];
        $click = null;

        while ($this->ffi->SDL_PollEvent(FFI::addr($this->event)) !== 0) {
            $type = $this->event->type;

            if ($type === self::SDL_QUIT) {
                $quit = true;
                continue;
            }

            if ($type === self::SDL_KEYDOWN) {
                // Layout: type(4) timestamp(4) windowID(4) state(1) repeat(1) padding(2) scancode(4) sym(4)
                $sym = $this->readI32(20);
                if ($sym === self::SDLK_ESCAPE) {
                    $escape = true;
                    $keys[] = 'escape';
                } elseif ($sym === self::SDLK_RETURN) {
                    $enter = true;
                    $keys[] = 'enter';
                } elseif ($sym === self::SDLK_SPACE) {
                    $keys[] = 'space';
                } elseif ($sym >= 97 && $sym <= 122) {
                    $keys[] = chr($sym); // a-z
                } elseif ($sym >= 48 && $sym <= 57) {
                    $keys[] = chr($sym); // 0-9
                } elseif ($sym === 1073741906) {
                    $keys[] = 'up';
                } elseif ($sym === 1073741905) {
                    $keys[] = 'down';
                } elseif ($sym === 1073741904) {
                    $keys[] = 'left';
                } elseif ($sym === 1073741903) {
                    $keys[] = 'right';
                }
                continue;
            }

            if ($type === self::SDL_MOUSEBUTTONDOWN) {
                $button = $this->readU8(16);
                if ($button === self::SDL_BUTTON_LEFT || $button === self::SDL_BUTTON_RIGHT) {
                    $click = [
                        'x' => $this->readI32(20),
                        'y' => $this->readI32(24),
                        'button' => $button,
                    ];
                }
            }
        }

        return [
            'quit' => $quit,
            'escape' => $escape,
            'enter' => $enter,
            'keys' => $keys,
            'click' => $click,
        ];
    }

    public function setDrawColor(int $r, int $g, int $b, int $a = 255): void
    {
        $this->ffi->SDL_SetRenderDrawColor($this->renderer, $r, $g, $b, $a);
    }

    public function clear(): void
    {
        $this->ffi->SDL_RenderClear($this->renderer);
    }

    public function fillRect(int $x, int $y, int $w, int $h): void
    {
        $this->rect->x = $x;
        $this->rect->y = $y;
        $this->rect->w = $w;
        $this->rect->h = $h;
        $this->ffi->SDL_RenderFillRect($this->renderer, FFI::addr($this->rect));
    }

    public function drawLine(int $x1, int $y1, int $x2, int $y2): void
    {
        $this->ffi->SDL_RenderDrawLine($this->renderer, $x1, $y1, $x2, $y2);
    }

    /**
     * Filled isometric diamond centered at (cx, cy).
     */
    public function fillDiamond(int $cx, int $cy, int $halfW, int $halfH): void
    {
        for ($dy = -$halfH; $dy <= $halfH; $dy++) {
            $t = $halfH === 0 ? 0.0 : (1.0 - (abs($dy) / $halfH));
            $hw = (int) round($halfW * $t);
            if ($hw < 1) {
                $hw = 1;
            }
            $this->fillRect($cx - $hw, $cy + $dy, $hw * 2, 1);
        }
    }

    public function present(): void
    {
        $this->ffi->SDL_RenderPresent($this->renderer);
    }

    public function delay(int $ms): void
    {
        if ($ms > 0) {
            $this->ffi->SDL_Delay($ms);
        }
    }

    public function shutdown(): void
    {
        if ($this->renderer !== null) {
            $this->ffi->SDL_DestroyRenderer($this->renderer);
            $this->renderer = null;
        }
        if ($this->window !== null) {
            $this->ffi->SDL_DestroyWindow($this->window);
            $this->window = null;
        }
        $this->ffi->SDL_Quit();
    }

    private function resolveLibrary(): string
    {
        $candidates = [
            getenv('SDL2_LIB') ?: '',
            '/opt/homebrew/lib/libSDL2.dylib',
            '/usr/local/lib/libSDL2.dylib',
            'libSDL2.dylib',
            'libSDL2.so',
            'libSDL2.so.0',
            'SDL2.dll',
        ];

        foreach ($candidates as $path) {
            if ($path === '') {
                continue;
            }
            if (str_contains($path, '/') || str_contains($path, '\\')) {
                if (is_file($path)) {
                    return $path;
                }
            } else {
                return $path;
            }
        }

        $this->fail(
            "SDL2 library not found. Install SDL2 (e.g. brew install sdl2) "
            . 'or set SDL2_LIB to the full path of libSDL2.'
        );
    }

    private function cdef(): string
    {
        return <<<'C'
            int SDL_Init(unsigned int flags);
            void SDL_Quit(void);
            const char *SDL_GetError(void);
            void *SDL_CreateWindow(const char *title, int x, int y, int w, int h, unsigned int flags);
            void SDL_DestroyWindow(void *window);
            void *SDL_CreateRenderer(void *window, int index, unsigned int flags);
            void SDL_DestroyRenderer(void *renderer);
            int SDL_SetRenderDrawColor(void *renderer, unsigned char r, unsigned char g, unsigned char b, unsigned char a);
            int SDL_RenderClear(void *renderer);
            int SDL_RenderPresent(void *renderer);
            void SDL_Delay(unsigned int ms);
            typedef struct SDL_Rect { int x, y, w, h; } SDL_Rect;
            int SDL_RenderFillRect(void *renderer, const SDL_Rect *rect);
            int SDL_RenderDrawLine(void *renderer, int x1, int y1, int x2, int y2);
            typedef struct SDL_Event {
                unsigned int type;
                unsigned char padding[52];
            } SDL_Event;
            int SDL_PollEvent(SDL_Event *event);
        C;
    }

    private function error(): string
    {
        $ptr = $this->ffi->SDL_GetError();
        if ($ptr === null) {
            return '(unknown)';
        }
        if (is_string($ptr)) {
            return $ptr === '' ? '(unknown)' : $ptr;
        }

        return FFI::string($ptr);
    }

    private function readU8(int $offset): int
    {
        return (int) $this->event->padding[$offset - 4];
    }

    private function readI32(int $offset): int
    {
        // offset relative to start of SDL_Event; padding starts at byte 4
        $p = $offset - 4;
        $b0 = (int) $this->event->padding[$p];
        $b1 = (int) $this->event->padding[$p + 1];
        $b2 = (int) $this->event->padding[$p + 2];
        $b3 = (int) $this->event->padding[$p + 3];
        $u = $b0 | ($b1 << 8) | ($b2 << 16) | ($b3 << 24);

        if ($u >= 0x80000000) {
            return $u - 0x100000000;
        }

        return $u;
    }

    private function fail(string $message): never
    {
        fwrite(STDERR, 'Diablo PHP error: ' . $message . PHP_EOL);
        exit(1);
    }
}
