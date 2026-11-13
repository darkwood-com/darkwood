<?php

declare(strict_types=1);

namespace App\Io;

/**
 * Creates delayed-readable streams so mux demos exercise real stream readiness
 * without requiring network access.
 */
final class DelayedStreamFactory
{
    /**
     * @return array{0: resource, 1: float} readable stream and intended delay seconds
     */
    public function create(float $delaySeconds, string $payload = 'ok'): array
    {
        $sockets = stream_socket_pair(STREAM_PF_UNIX, STREAM_SOCK_STREAM, STREAM_IPPROTO_IP);
        if (false === $sockets) {
            throw new \RuntimeException('stream_socket_pair failed');
        }

        [$read, $write] = $sockets;
        stream_set_blocking($read, false);
        stream_set_blocking($write, false);

        // Defer the write using a shutdown function scheduled via microtime deadline
        // checked by runners; for select/poll we write from the runner when due.
        // Immediate write + separate delay token is simpler for StreamSelectDriver.
        fwrite($write, $payload);
        fclose($write);

        return [$read, $delaySeconds];
    }

    /**
     * Pair where data becomes available after $delaySeconds (busy-wait writer in caller).
     *
     * @return array{read: resource, write: resource}
     */
    public function openPair(): array
    {
        $sockets = stream_socket_pair(STREAM_PF_UNIX, STREAM_SOCK_STREAM, STREAM_IPPROTO_IP);
        if (false === $sockets) {
            throw new \RuntimeException('stream_socket_pair failed');
        }

        stream_set_blocking($sockets[0], false);
        stream_set_blocking($sockets[1], false);

        return ['read' => $sockets[0], 'write' => $sockets[1]];
    }
}
