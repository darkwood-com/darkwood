<?php

declare(strict_types=1);

namespace Flow\Examples\Model;

class UserData
{
    /**
     * @param null|array<string, mixed> $availabilities
     * @param null|array<string, mixed> $posts
     */
    public function __construct(
        public int $id,
        public string $name,
        public string $email,
        public ?array $availabilities = null,
        public ?array $posts = null
    ) {}
}
