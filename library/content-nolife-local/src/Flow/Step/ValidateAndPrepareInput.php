<?php

declare(strict_types=1);

namespace App\Flow\Step;

use App\Flow\Model\DepthPipelineState;
use Flow\JobInterface;
use RuntimeException;
use Symfony\Component\Uid\Uuid;

/**
 * @implements JobInterface<DepthPipelineState, DepthPipelineState>
 */
final class ValidateAndPrepareInput implements JobInterface
{
    private const MAX_BYTES = 20 * 1024 * 1024;
    private const MAX_DIMENSION = 4096;

    public function __invoke($state): DepthPipelineState
    {
        if (!$state->upload->isValid()) {
            throw new RuntimeException('Invalid upload.');
        }

        $size = $state->upload->getSize();
        if ($size !== false && $size > self::MAX_BYTES) {
            throw new RuntimeException('Image is too large (maximum 20 MB).');
        }

        $mime = (string) $state->upload->getMimeType();
        if (!in_array($mime, ['image/jpeg', 'image/png', 'image/webp'], true)) {
            throw new RuntimeException('Only JPEG, PNG, and WebP images are supported.');
        }

        $state->modelPath = match ($state->variant) {
            'f32' => $state->modelPathF32,
            default => $state->modelPathQ4k,
        };
        $state->modelLabel = match ($state->variant) {
            'f32' => 'DA3-BASE f32',
            default => 'DA3-BASE q4_k',
        };

        $state->id = Uuid::v4()->toRfc4122();
        $state->workDir = rtrim($state->inferenceDir, '/').'/'.$state->id;
        if (!mkdir($state->workDir, 0775, true) && !is_dir($state->workDir)) {
            throw new RuntimeException('Unable to create inference work directory.');
        }

        $extension = match ($mime) {
            'image/png' => 'png',
            'image/webp' => 'webp',
            default => 'jpg',
        };
        $state->inputFilename = 'input.'.$extension;
        $state->inputPath = $state->workDir.'/'.$state->inputFilename;
        $source = $state->upload->getRealPath() ?: $state->upload->getPathname();
        if (!copy($source, $state->inputPath)) {
            throw new RuntimeException('Unable to copy input image into work directory.');
        }

        $info = getimagesize($state->inputPath);
        if ($info === false) {
            throw new RuntimeException('Unable to read uploaded image dimensions.');
        }
        $width = (int) $info[0];
        $height = (int) $info[1];
        if ($width > self::MAX_DIMENSION || $height > self::MAX_DIMENSION) {
            throw new RuntimeException(sprintf(
                'Image dimensions too large (maximum %d×%d pixels).',
                self::MAX_DIMENSION,
                self::MAX_DIMENSION,
            ));
        }
        $state->sourceSize = [$width, $height];

        return $state;
    }
}
