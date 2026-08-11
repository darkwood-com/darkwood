<?php

declare(strict_types=1);

namespace Quake3\Platform;

use FFI;
use FFI\CData;

/**
 * Thin SDL2 + OpenGL context boundary (ioq3 sdl_glimp.c subset).
 * PHP owns the game loop; this class only window / events / swap.
 */
final class SdlGl
{
    private const SDL_INIT_VIDEO = 0x00000020;
    private const SDL_WINDOWPOS_CENTERED = 0x1FFF0000;
    private const SDL_WINDOW_OPENGL = 0x00000002;
    private const SDL_WINDOW_RESIZABLE = 0x00000020;
    private const SDL_QUIT = 0x100;
    private const SDL_KEYDOWN = 0x300;
    private const SDL_KEYUP = 0x301;
    private const SDL_MOUSEMOTION = 0x400;
    private const SDL_MOUSEBUTTONDOWN = 0x401;
    private const SDL_MOUSEBUTTONUP = 0x402;
    private const SDL_MOUSEWHEEL = 0x403;

    private const SDL_GL_RED_SIZE = 0;
    private const SDL_GL_GREEN_SIZE = 1;
    private const SDL_GL_BLUE_SIZE = 2;
    private const SDL_GL_ALPHA_SIZE = 3;
    private const SDL_GL_DEPTH_SIZE = 6;
    private const SDL_GL_DOUBLEBUFFER = 5;

    private const SDLK_ESCAPE = 27;
    private const SDLK_w = 119;
    private const SDLK_a = 97;
    private const SDLK_s = 115;
    private const SDLK_d = 100;
    private const SDLK_SPACE = 32;
    private const SDLK_LSHIFT = 1073742049;
    private const SDLK_RSHIFT = 1073742053;

    private FFI $ffi;
    private ?CData $window = null;
    private ?CData $glContext = null;
    private CData $event;
    private int $width;
    private int $height;

    /** @var array<string, bool> */
    private array $keysDown = [];

    public function __construct()
    {
        if (!extension_loaded('ffi')) {
            $this->fail('ext-ffi is not loaded. Run with: php -d ffi.enable=true bin/quake3.php …');
        }
        if (ini_get('ffi.enable') === '0' || ini_get('ffi.enable') === 'false') {
            $this->fail('FFI is disabled. Run with: php -d ffi.enable=true bin/quake3.php …');
        }

        $library = $this->resolveLibrary();
        try {
            $this->ffi = FFI::cdef($this->cdef(), $library);
        } catch (\Throwable $e) {
            $this->fail('Failed to load SDL2 via FFI (' . $library . '): ' . $e->getMessage());
        }

        $this->event = $this->ffi->new('SDL_Event');
        $this->width = 1280;
        $this->height = 720;
    }

    public function init(string $title, int $width = 1280, int $height = 720): void
    {
        $this->width = $width;
        $this->height = $height;

        if ($this->ffi->SDL_Init(self::SDL_INIT_VIDEO) !== 0) {
            $this->fail('SDL_Init failed: ' . $this->error());
        }

        $this->ffi->SDL_GL_SetAttribute(self::SDL_GL_RED_SIZE, 8);
        $this->ffi->SDL_GL_SetAttribute(self::SDL_GL_GREEN_SIZE, 8);
        $this->ffi->SDL_GL_SetAttribute(self::SDL_GL_BLUE_SIZE, 8);
        // Needed for pewter_shiney LM pass: blendFunc DST_COLOR / ONE_MINUS_DST_ALPHA
        $this->ffi->SDL_GL_SetAttribute(self::SDL_GL_ALPHA_SIZE, 8);
        $this->ffi->SDL_GL_SetAttribute(self::SDL_GL_DEPTH_SIZE, 24);
        $this->ffi->SDL_GL_SetAttribute(self::SDL_GL_DOUBLEBUFFER, 1);

        $flags = self::SDL_WINDOW_OPENGL | self::SDL_WINDOW_RESIZABLE;
        $this->window = $this->ffi->SDL_CreateWindow(
            $title,
            self::SDL_WINDOWPOS_CENTERED,
            self::SDL_WINDOWPOS_CENTERED,
            $width,
            $height,
            $flags,
        );
        if ($this->window === null) {
            $this->fail('SDL_CreateWindow failed: ' . $this->error());
        }

        $this->glContext = $this->ffi->SDL_GL_CreateContext($this->window);
        if ($this->glContext === null) {
            $this->fail('SDL_GL_CreateContext failed: ' . $this->error());
        }

        $this->ffi->SDL_GL_SetSwapInterval(1);
        $this->ffi->SDL_SetRelativeMouseMode(1);
    }

    public function getWidth(): int
    {
        return $this->width;
    }

    public function getHeight(): int
    {
        return $this->height;
    }

    public function swap(): void
    {
        if ($this->window !== null) {
            $this->ffi->SDL_GL_SwapWindow($this->window);
        }
    }

    public function delay(int $ms): void
    {
        $this->ffi->SDL_Delay(max(0, $ms));
    }

    public function ticks(): int
    {
        return (int) $this->ffi->SDL_GetTicks();
    }

    private bool $mouseLeft = false;
    private bool $mouseRight = false;
    private bool $mouseMiddle = false;

    /**
     * @return array{
     *   quit: bool,
     *   escape: bool,
     *   keysDown: array<string, bool>,
     *   mouseDx: int,
     *   mouseDy: int,
     *   keysPressed: list<string>,
     *   mouseLeft: bool,
     *   fire: bool,
     *   weapnext: bool,
     *   weapprev: bool,
     *   strafe: bool,
     *   zoom: bool
     * }
     */
    public function pollEvents(): array
    {
        $quit = false;
        $escape = false;
        $mouseDx = 0;
        $mouseDy = 0;
        $keysPressed = [];
        $fire = false;
        $weapnext = false;
        $weapprev = false;

        while ($this->ffi->SDL_PollEvent(FFI::addr($this->event)) !== 0) {
            $type = $this->event->type;

            if ($type === self::SDL_QUIT) {
                $quit = true;
                continue;
            }

            if ($type === self::SDL_KEYDOWN || $type === self::SDL_KEYUP) {
                $sym = $this->readI32(20);
                $down = $type === self::SDL_KEYDOWN;
                $name = $this->keyName($sym);
                if ($name !== null) {
                    $this->keysDown[$name] = $down;
                    if ($down) {
                        $keysPressed[] = $name;
                    }
                }
                if ($down && $sym === self::SDLK_ESCAPE) {
                    $escape = true;
                }
                continue;
            }

            if ($type === self::SDL_MOUSEMOTION) {
                $mouseDx += $this->readI32(28);
                $mouseDy += $this->readI32(32);
            }

            if ($type === self::SDL_MOUSEBUTTONDOWN || $type === self::SDL_MOUSEBUTTONUP) {
                // SDL_MouseButtonEvent button at absolute offset 16 → padding[12]
                $button = (int) $this->event->padding[12];
                $down = $type === self::SDL_MOUSEBUTTONDOWN;
                if ($button === 1) {
                    $this->mouseLeft = $down;
                    if ($down) {
                        $fire = true;
                    }
                }
                if ($button === 2) {
                    $this->mouseMiddle = $down; // SDL_BUTTON_MIDDLE — +zoom
                }
                if ($button === 3) {
                    $this->mouseRight = $down; // SDL_BUTTON_RIGHT — +strafe
                }
            }

            if ($type === self::SDL_MOUSEWHEEL) {
                // SDL_MouseWheelEvent.y at absolute offset 20 (padding[16])
                $y = $this->readI32(20);
                if ($y > 0) {
                    $weapprev = true; // MWHEELUP weapprev
                } elseif ($y < 0) {
                    $weapnext = true; // MWHEELDOWN weapnext
                }
            }
        }

        if (!empty($this->keysDown['ctrl']) || !empty($this->keysDown['f'])) {
            $fire = true;
        }

        return [
            'quit' => $quit,
            'escape' => $escape,
            'keysDown' => $this->keysDown,
            'mouseDx' => $mouseDx,
            'mouseDy' => $mouseDy,
            'keysPressed' => $keysPressed,
            'mouseLeft' => $this->mouseLeft,
            'fire' => $fire || $this->mouseLeft,
            'weapnext' => $weapnext,
            'weapprev' => $weapprev,
            'strafe' => $this->mouseRight || !empty($this->keysDown['alt']),
            'zoom' => $this->mouseMiddle,
        ];
    }

    public function shutdown(): void
    {
        $this->ffi->SDL_SetRelativeMouseMode(0);
        if ($this->glContext !== null) {
            $this->ffi->SDL_GL_DeleteContext($this->glContext);
            $this->glContext = null;
        }
        if ($this->window !== null) {
            $this->ffi->SDL_DestroyWindow($this->window);
            $this->window = null;
        }
        $this->ffi->SDL_Quit();
    }

    private function keyName(int $sym): ?string
    {
        return match ($sym) {
            self::SDLK_ESCAPE => 'escape',
            self::SDLK_w => 'w',
            self::SDLK_a => 'a',
            self::SDLK_s => 's',
            self::SDLK_d => 'd',
            self::SDLK_SPACE => 'space',
            self::SDLK_LSHIFT, self::SDLK_RSHIFT => 'shift',
            1073742048, 1073742052 => 'ctrl', // LCTRL RCTRL
            1073742050, 1073742054 => 'alt', // LALT RALT — +strafe
            102 => 'f',
            114 => 'r', // respawn
            1073741906 => 'up',
            1073741905 => 'down',
            1073741904 => 'left',
            1073741903 => 'right',
            1073741902 => 'pgdn', // SDLK_PAGEDOWN — +lookup
            1073741901 => 'end',  // SDLK_END — centerview
            127 => 'del',         // SDLK_DELETE — +lookdown
            49 => '1',
            50 => '2',
            51 => '3',
            52 => '4',
            53 => '5',
            54 => '6',
            55 => '7',
            56 => '8',
            57 => '9',
            91 => 'lbracket',  // weapprev
            93 => 'rbracket',  // weapnext
            107 => 'k', // hurt test
            112 => 'p', // surface pick
            99 => 'c', // crouch (+movedown)
            default => null,
        };
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

        $this->fail('SDL2 library not found. Install SDL2 (brew install sdl2) or set SDL2_LIB.');
    }

    private function cdef(): string
    {
        return <<<'C'
            int SDL_Init(unsigned int flags);
            void SDL_Quit(void);
            const char *SDL_GetError(void);
            void *SDL_CreateWindow(const char *title, int x, int y, int w, int h, unsigned int flags);
            void SDL_DestroyWindow(void *window);
            int SDL_GL_SetAttribute(int attr, int value);
            void *SDL_GL_CreateContext(void *window);
            void SDL_GL_DeleteContext(void *context);
            void SDL_GL_SwapWindow(void *window);
            int SDL_GL_SetSwapInterval(int interval);
            void SDL_Delay(unsigned int ms);
            unsigned int SDL_GetTicks(void);
            int SDL_SetRelativeMouseMode(int enabled);
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

    private function readI32(int $offset): int
    {
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
        fwrite(STDERR, 'Quake3 PHP error: ' . $message . PHP_EOL);
        exit(1);
    }
}
