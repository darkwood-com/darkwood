<?php

declare(strict_types=1);

namespace Quake3\Http;

/**
 * Minimal HTTP/1.1 GET server for local dev (FIRST_TRIANGLES).
 * Serves static files from public/ and /api/mesh.json from PHP-extracted BSP mesh.
 */
final class DevServer
{
    public function __construct(
        private readonly string $host,
        private readonly int $port,
        private readonly string $publicDir,
        private readonly string $meshJson,
        private readonly string $mapLabel,
    ) {
    }

    public function run(): never
    {
        $addr = sprintf('tcp://%s:%d', $this->host, $this->port);
        $socket = @stream_socket_server($addr, $errno, $errstr);
        if ($socket === false) {
            throw new \RuntimeException(sprintf('Cannot bind %s: %s (%d)', $addr, $errstr, $errno));
        }

        stream_set_blocking($socket, true);
        fwrite(STDERR, sprintf("Quake III PHP dev server\n"));
        fwrite(STDERR, sprintf("  map:  %s\n", $this->mapLabel));
        fwrite(STDERR, sprintf("  url:  http://%s:%d/\n", $this->host, $this->port));
        fwrite(STDERR, "  Ctrl+C to stop\n\n");

        while (true) {
            $client = @stream_socket_accept($socket, -1);
            if ($client === false) {
                continue;
            }
            $this->handleClient($client);
            fclose($client);
        }
    }

    private function handleClient(mixed $client): void
    {
        $request = fgets($client);
        if ($request === false) {
            return;
        }

        if (!preg_match('#^GET ([^ ]+) HTTP/#', $request, $m)) {
            $this->respond($client, 400, 'text/plain', 'Bad Request');
            return;
        }

        $path = rawurldecode($m[1]);
        if (str_contains($path, '..')) {
            $this->respond($client, 403, 'text/plain', 'Forbidden');
            return;
        }

        // Drain headers
        while (($line = fgets($client)) !== false) {
            if ($line === "\r\n" || $line === "\n") {
                break;
            }
        }

        if ($path === '/api/mesh.json') {
            $this->respond($client, 200, 'application/json', $this->meshJson);
            return;
        }

        if ($path === '/' || $path === '') {
            $path = '/index.html';
        }

        $file = $this->publicDir . $path;
        if (!is_file($file)) {
            $this->respond($client, 404, 'text/plain', 'Not Found');
            return;
        }

        $body = file_get_contents($file);
        if ($body === false) {
            $this->respond($client, 500, 'text/plain', 'Read error');
            return;
        }

        $this->respond($client, 200, $this->mimeType($file), $body);
    }

    private function respond(mixed $client, int $code, string $contentType, string $body): void
    {
        $status = match ($code) {
            200 => 'OK',
            400 => 'Bad Request',
            403 => 'Forbidden',
            404 => 'Not Found',
            500 => 'Internal Server Error',
            default => 'Unknown',
        };

        $header = sprintf(
            "HTTP/1.1 %d %s\r\nContent-Type: %s\r\nContent-Length: %d\r\nConnection: close\r\n\r\n",
            $code,
            $status,
            $contentType,
            strlen($body),
        );
        fwrite($client, $header . $body);
    }

    private function mimeType(string $path): string
    {
        return match (strtolower(pathinfo($path, PATHINFO_EXTENSION))) {
            'html' => 'text/html; charset=utf-8',
            'js' => 'text/javascript; charset=utf-8',
            'css' => 'text/css; charset=utf-8',
            'json' => 'application/json',
            default => 'application/octet-stream',
        };
    }
}
