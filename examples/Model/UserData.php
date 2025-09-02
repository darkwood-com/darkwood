<?php

declare(strict_types=1);

namespace Flow\Examples\Model;

class UserData
{
    /**
     * @param array<string, mixed>|null $availabilities
     * @param array<string, mixed>|null $posts
     */
    public function __construct(
        public int $id,
        public string $name,
        public string $email,
        public ?array $availabilities = null,
        public ?array $posts = null
    ) {}
}
