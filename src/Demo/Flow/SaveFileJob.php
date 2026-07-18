<?php

declare(strict_types=1);

namespace App\Demo\Flow;

use App\Demo\Model\HashedContent;
use App\Demo\Model\ImageResult;
use App\Demo\Trace;
use Flow\JobInterface;

/**
 * @implements JobInterface<HashedContent, ImageResult>
 */
final class SaveFileJob implements JobInterface
{
    public function __construct(
        private readonly string $outputDir,
        private readonly Trace $trace,
    ) {
    }

    public function __invoke($data): ImageResult
    {
        /** @var HashedContent $data */
        if ($data->error !== null || $data->body === null || $data->checksum === null) {
            return new ImageResult(
                url: $data->url,
                path: null,
                checksum: null,
                success: false,
                error: $data->error ?? 'empty body',
            );
        }

        if (!is_dir($this->outputDir) && !mkdir($this->outputDir, 0775, true) && !is_dir($this->outputDir)) {
            throw new \RuntimeException(sprintf('Unable to create output directory "%s".', $this->outputDir));
        }

        $name = $this->filenameForUrl($data->url);
        $path = rtrim($this->outputDir, DIRECTORY_SEPARATOR).DIRECTORY_SEPARATOR.$name;
        if (file_put_contents($path, $data->body) === false) {
            throw new \RuntimeException(sprintf('Unable to write "%s".', $path));
        }

        $this->trace->emit('item.completed', [
            'url' => $data->url,
            'checksum' => $data->checksum,
            'path' => $path,
        ]);

        return new ImageResult(
            url: $data->url,
            path: $path,
            checksum: $data->checksum,
            success: true,
            error: null,
        );
    }

    private function filenameForUrl(string $url): string
    {
        $path = parse_url($url, PHP_URL_PATH);
        $base = is_string($path) ? basename($path) : '';
        if ($base === '' || $base === '/' || $base === '.') {
            $base = 'image.bin';
        }

        return substr(hash('sha256', $url), 0, 12).'_'.$base;
    }
}
