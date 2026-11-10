<?php

declare(strict_types=1);

namespace App\Service;

use RuntimeException;

final class DepthColormapRenderer
{
    /**
     * @param list<float> $values
     */
    public function toGrayscalePng(
        array $values,
        int $width,
        int $height,
        bool $invert = true,
        ?float $min = null,
        ?float $max = null,
    ): string {
        $this->assertGd();
        if ($width <= 0 || $height <= 0 || count($values) !== $width * $height) {
            throw new RuntimeException('Invalid depth dimensions for PNG export.');
        }

        $min ??= min($values);
        $max ??= max($values);
        $range = max($max - $min, 1e-8);

        $image = imagecreatetruecolor($width, $height);
        if ($image === false) {
            throw new RuntimeException('Unable to create GD image.');
        }

        $i = 0;
        for ($y = 0; $y < $height; ++$y) {
            for ($x = 0; $x < $width; ++$x) {
                $t = ($values[$i] - $min) / $range;
                if ($invert) {
                    $t = 1.0 - $t;
                }
                $gray = (int) round(max(0, min(255, $t * 255)));
                $color = imagecolorallocate($image, $gray, $gray, $gray);
                imagesetpixel($image, $x, $y, $color);
                ++$i;
            }
        }

        ob_start();
        imagepng($image);
        $png = ob_get_clean();
        if ($png === false) {
            throw new RuntimeException('Failed to encode PNG.');
        }

        return $png;
    }

    /**
     * @param list<float> $values
     */
    public function toTurboPng(
        array $values,
        int $width,
        int $height,
        ?float $min = null,
        ?float $max = null,
    ): string {
        $this->assertGd();
        if ($width <= 0 || $height <= 0 || count($values) !== $width * $height) {
            throw new RuntimeException('Invalid depth dimensions for PNG export.');
        }

        $min ??= min($values);
        $max ??= max($values);
        $range = max($max - $min, 1e-8);

        $image = imagecreatetruecolor($width, $height);
        if ($image === false) {
            throw new RuntimeException('Unable to create GD image.');
        }

        $i = 0;
        for ($y = 0; $y < $height; ++$y) {
            for ($x = 0; $x < $width; ++$x) {
                $t = ($values[$i] - $min) / $range;
                [$r, $g, $b] = $this->turbo($t);
                $color = imagecolorallocate($image, $r, $g, $b);
                imagesetpixel($image, $x, $y, $color);
                ++$i;
            }
        }

        ob_start();
        imagepng($image);
        $png = ob_get_clean();
        if ($png === false) {
            throw new RuntimeException('Failed to encode PNG.');
        }

        return $png;
    }

    /** @return array{0:int,1:int,2:int} */
    private function turbo(float $t): array
    {
        $t = max(0.0, min(1.0, $t));
        $r = (int) round(255 * max(0.0, min(1.0, 1.5 * $t)));
        $g = (int) round(255 * max(0.0, min(1.0, 1.5 - abs(2.0 * $t - 1.0))));
        $b = (int) round(255 * max(0.0, min(1.0, 1.5 * (1.0 - $t))));

        return [$r, $g, $b];
    }

    private function assertGd(): void
    {
        if (!function_exists('imagecreatetruecolor')) {
            throw new RuntimeException('GD extension is required for depth visualization.');
        }
    }
}
