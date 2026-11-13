<?php

declare(strict_types=1);

namespace App\Demo\Flow;

use App\Demo\Model\FetchedContent;
use App\Demo\Model\FetchRequest;
use App\Demo\Trace;
use Flow\Driver\StreamSelectDriver;
use Flow\JobInterface;
use Generator;

/**
 * Demo HTTP GET job using StreamSelectDriver wait tokens (Generator + stream_select).
 * Protocol knowledge stays in the job — the driver only multiplexes streams.
 *
 * @implements JobInterface<FetchRequest, FetchedContent|Generator>
 */
final class FetchImageJob implements JobInterface
{
    public function __construct(
        private readonly StreamSelectDriver $driver,
        private readonly Trace $trace,
    ) {
    }

    public function __invoke($data): Generator
    {
        /** @var FetchRequest $data */
        $this->trace->emit('item.started', ['url' => $data->url]);

        $stream = null;
        try {
            $opened = $this->open($data->url, $data->timeoutSeconds);
            if ($opened['error'] !== null) {
                $this->trace->emit('item.failed', ['url' => $data->url, 'error' => $opened['error']]);

                return new FetchedContent($data->url, null, $opened['error']);
            }

            $stream = $opened['stream'];
            $deadline = microtime(true) + $data->timeoutSeconds;

            if (!(yield from $this->waitUntil($stream, 'write', $deadline))) {
                $this->trace->emit('item.failed', ['url' => $data->url, 'error' => 'timeout']);

                return new FetchedContent($data->url, null, 'timeout');
            }

            if (!$this->sendRequest($stream, $data->url)) {
                $this->trace->emit('item.failed', ['url' => $data->url, 'error' => 'failed to send request']);

                return new FetchedContent($data->url, null, 'failed to send request');
            }

            $buffer = '';
            while (true) {
                if (!(yield from $this->waitUntil($stream, 'read', $deadline))) {
                    $this->trace->emit('item.failed', ['url' => $data->url, 'error' => 'timeout']);

                    return new FetchedContent($data->url, null, 'timeout');
                }

                $chunk = fread($stream, 8192);
                if ($chunk === false || $chunk === '') {
                    if (feof($stream)) {
                        break;
                    }
                    continue;
                }

                $buffer .= $chunk;
                if ($this->isComplete($buffer)) {
                    break;
                }
            }

            $parsed = $this->parseResponse($buffer);
            if ($parsed['error'] !== null) {
                $this->trace->emit('item.failed', ['url' => $data->url, 'error' => $parsed['error']]);

                return new FetchedContent($data->url, null, $parsed['error']);
            }

            return new FetchedContent($data->url, $parsed['body'], null);
        } finally {
            if (is_resource($stream)) {
                fclose($stream);
            }
        }
    }

    /**
     * @param resource $stream
     * @param 'read'|'write' $mode
     *
     * @return Generator<int, mixed, mixed, bool>
     */
    private function waitUntil($stream, string $mode, float $deadline): Generator
    {
        $remaining = $deadline - microtime(true);
        if ($remaining <= 0) {
            return false;
        }

        $ready = (yield $mode === 'read'
            ? $this->driver->waitReadable($stream, $remaining)
            : $this->driver->waitWritable($stream, $remaining));

        return (bool) $ready;
    }

    /**
     * @return array{stream: ?resource, error: ?string}
     */
    private function open(string $url, float $timeoutSeconds): array
    {
        $parts = parse_url($url);
        if ($parts === false || !isset($parts['host'], $parts['scheme'])) {
            return ['stream' => null, 'error' => 'invalid url'];
        }

        $scheme = strtolower($parts['scheme']);
        if (!in_array($scheme, ['http', 'https'], true)) {
            return ['stream' => null, 'error' => 'unsupported scheme'];
        }

        $host = $parts['host'];
        $port = $parts['port'] ?? ($scheme === 'https' ? 443 : 80);
        $remote = ($scheme === 'https' ? 'ssl' : 'tcp').'://'.$host.':'.$port;

        $context = null;
        if ($scheme === 'https') {
            $context = stream_context_create([
                'ssl' => [
                    'verify_peer' => true,
                    'verify_peer_name' => true,
                    'SNI_enabled' => true,
                    'peer_name' => $host,
                ],
            ]);
        }

        $errno = 0;
        $errstr = '';
        $stream = @stream_socket_client(
            $remote,
            $errno,
            $errstr,
            $timeoutSeconds,
            STREAM_CLIENT_CONNECT,
            $context,
        );

        if ($stream === false) {
            return ['stream' => null, 'error' => $errstr !== '' ? $errstr : 'connection failed'];
        }

        stream_set_blocking($stream, false);

        return ['stream' => $stream, 'error' => null];
    }

    /**
     * @param resource $stream
     */
    private function sendRequest($stream, string $url): bool
    {
        $parts = parse_url($url);
        if ($parts === false || !isset($parts['host'])) {
            return false;
        }

        $path = $parts['path'] ?? '/';
        if (isset($parts['query'])) {
            $path .= '?'.$parts['query'];
        }

        $request = sprintf(
            "GET %s HTTP/1.1\r\nHost: %s\r\nUser-Agent: FlowDemo/1.0\r\nAccept: */*\r\nConnection: close\r\n\r\n",
            $path,
            $parts['host'],
        );

        $written = @fwrite($stream, $request);

        return $written !== false && $written > 0;
    }

    private function isComplete(string $buffer): bool
    {
        $headerEnd = strpos($buffer, "\r\n\r\n");
        if ($headerEnd === false) {
            return false;
        }

        $headers = substr($buffer, 0, $headerEnd);
        $body = substr($buffer, $headerEnd + 4);

        if (preg_match('/^Transfer-Encoding:\s*chunked/im', $headers) === 1) {
            return str_contains($body, "0\r\n\r\n");
        }

        if (preg_match('/^Content-Length:\s*(\d+)/im', $headers, $matches) === 1) {
            return strlen($body) >= (int) $matches[1];
        }

        return false;
    }

    /**
     * @return array{body: ?string, error: ?string}
     */
    private function parseResponse(string $buffer): array
    {
        $headerEnd = strpos($buffer, "\r\n\r\n");
        if ($headerEnd === false) {
            return ['body' => null, 'error' => 'incomplete response'];
        }

        $headers = substr($buffer, 0, $headerEnd);
        $body = substr($buffer, $headerEnd + 4);

        if (preg_match('/^HTTP\/\d(?:\.\d)?\s+(\d+)/', $headers, $matches) !== 1) {
            return ['body' => null, 'error' => 'invalid status line'];
        }

        $status = (int) $matches[1];
        if ($status < 200 || $status >= 300) {
            return ['body' => null, 'error' => 'HTTP '.$status];
        }

        if (preg_match('/^Transfer-Encoding:\s*chunked/im', $headers) === 1) {
            $decoded = $this->decodeChunked($body);
            if ($decoded === null) {
                return ['body' => null, 'error' => 'invalid chunked body'];
            }
            $body = $decoded;
        } elseif (preg_match('/^Content-Length:\s*(\d+)/im', $headers, $matches) === 1) {
            $body = substr($body, 0, (int) $matches[1]);
        }

        return ['body' => $body, 'error' => null];
    }

    private function decodeChunked(string $body): ?string
    {
        $decoded = '';
        $offset = 0;
        $length = strlen($body);

        while ($offset < $length) {
            $lineEnd = strpos($body, "\r\n", $offset);
            if ($lineEnd === false) {
                return null;
            }

            $sizeHex = trim(substr($body, $offset, $lineEnd - $offset));
            if ($sizeHex === '' || !ctype_xdigit($sizeHex)) {
                return null;
            }

            $size = hexdec($sizeHex);
            $offset = $lineEnd + 2;

            if ($size === 0) {
                return $decoded;
            }

            if ($offset + $size > $length) {
                return null;
            }

            $decoded .= substr($body, $offset, $size);
            $offset += $size + 2;
        }

        return null;
    }
}
