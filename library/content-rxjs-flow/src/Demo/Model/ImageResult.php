<?php

declare(strict_types=1);

namespace App\Demo\Model;

final readonly class ImageResult
{
    public function __construct(
        public string $url,
        public ?string $path,
        public ?string $checksum,
        public bool $success,
        public ?string $error,
    ) {
    }

    /**
     * @return array{url: string, path: ?string, checksum: ?string, success: bool, error: ?string}
     */
    public function toArray(): array
    {
        return [
            'url' => $this->url,
            'path' => $this->path,
            'checksum' => $this->checksum,
            'success' => $this->success,
            'error' => $this->error,
        ];
    }
}
