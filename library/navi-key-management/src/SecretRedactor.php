<?php

declare(strict_types=1);

namespace App;

/**
 * Strips a known secret out of a message. Used only when an upstream exception
 * might echo the API key.
 */
final class SecretRedactor
{
    public static function message(string $message, #[\SensitiveParameter] string $secret): string
    {
        if ($secret === '') {
            return $message;
        }

        return str_replace($secret, '[redacted]', $message);
    }
}
