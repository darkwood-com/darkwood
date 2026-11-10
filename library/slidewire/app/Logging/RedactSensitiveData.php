<?php

namespace App\Logging;

use Illuminate\Log\Logger;
use Monolog\LogRecord;
use Throwable;

class RedactSensitiveData
{
    private const KEYS = '/password|passwd|secret|token|authorization|cookie|app[_-]?key|api[_-]?key|private[_-]?key|credential|dsn|database[_-]?url|db[_-]?url|session/i';

    public function __invoke(Logger $logger): void
    {
        $logger->pushProcessor(fn (LogRecord $record) => $record->with(
            message: $this->text($record->message),
            context: $this->values($record->context),
            extra: $this->values($record->extra),
        ));
    }

    public function values(array $values, int $depth = 0): array
    {
        $clean = [];
        foreach ($values as $key => $value) {
            if ($depth > 8 || preg_match(self::KEYS, (string) $key)) {
                $clean[$key] = '[REDACTED]';
            } elseif ($value instanceof Throwable) {
                // Exception arguments, chained response bodies and traces can contain credentials.
                $clean[$key] = ['type' => get_class($value), 'message' => '[REDACTED]', 'file' => $value->getFile(), 'line' => $value->getLine()];
            } elseif (is_array($value)) {
                $clean[$key] = $this->values($value, $depth + 1);
            } elseif (is_string($value)) {
                $clean[$key] = $this->text($value);
            } elseif (is_object($value)) {
                $clean[$key] = '[object:'.get_class($value).']';
            } else {
                $clean[$key] = $value;
            }
        }

        return $clean;
    }

    public function text(string $text): string
    {
        $known = [config('app.key')];
        foreach (config('database.connections', []) as $connection) {
            $known[] = $connection['password'] ?? null;
            $known[] = $connection['url'] ?? null;
        }
        foreach ($known as $secret) {
            if (is_string($secret) && strlen($secret) >= 8) {
                $text = str_replace($secret, '[REDACTED]', $text);
            }
        }
        $text = preg_replace('~-----BEGIN [^-]*PRIVATE KEY-----.*?-----END [^-]*PRIVATE KEY-----~s', '[REDACTED PRIVATE KEY]', $text);
        $text = preg_replace('~\b([a-z][a-z0-9+.-]*://)[^\s/@]+@~i', '$1[REDACTED]@', $text);
        $text = preg_replace('/\bBearer\s+[^\s,;]+/i', 'Bearer [REDACTED]', $text);
        $text = preg_replace('/\b(Authorization|Proxy-Authorization|Cookie|Set-Cookie):[^\r\n]*/i', '$1: [REDACTED]', $text);
        $keys = '(?:password|passwd|secret|access_token|refresh_token|api[_-]?key|app[_-]?key|token|credential)';
        $text = preg_replace('/(\b'.$keys.'["\']?\s*[:=]\s*)(["\']).*?\2/i', '$1[REDACTED]', $text);

        return preg_replace('/(\b'.$keys.'["\']?\s*[:=]\s*)[^\s,;\x26"\']+/i', '$1[REDACTED]', $text);
    }
}
