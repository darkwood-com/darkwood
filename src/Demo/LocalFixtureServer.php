<?php

declare(strict_types=1);

namespace App\Demo;

/**
 * Starts a short-lived PHP built-in server for deterministic fixture HTTP.
 * Demo-only — not part of darkwood/flow.
 */
final class LocalFixtureServer
{
    /** @var resource|null */
    private $process = null;

    /** @var list<resource>|null */
    private ?array $pipes = null;

    public function __construct(
        private readonly string $documentRoot,
        private readonly string $host = '127.0.0.1',
        private readonly int $port = 8765,
    ) {
    }

    public function baseUrl(): string
    {
        return sprintf('http://%s:%d', $this->host, $this->port);
    }

    public function start(): void
    {
        if ($this->process !== null) {
            return;
        }

        $router = $this->documentRoot.DIRECTORY_SEPARATOR.'router.php';
        $command = [
            PHP_BINARY,
            '-S',
            sprintf('%s:%d', $this->host, $this->port),
            '-t',
            $this->documentRoot,
            $router,
        ];

        $descriptorspec = [
            0 => ['pipe', 'r'],
            1 => ['pipe', 'w'],
            2 => ['pipe', 'w'],
        ];

        $process = proc_open($command, $descriptorspec, $pipes, $this->documentRoot);
        if (!is_resource($process)) {
            throw new \RuntimeException('Unable to start fixture HTTP server.');
        }

        foreach ($pipes as $pipe) {
            stream_set_blocking($pipe, false);
        }

        $this->process = $process;
        $this->pipes = $pipes;

        $deadline = microtime(true) + 3.0;
        while (microtime(true) < $deadline) {
            if ($this->isReady()) {
                return;
            }
            usleep(50_000);
        }

        $this->stop();
        throw new \RuntimeException(sprintf('Fixture server did not become ready on %s.', $this->baseUrl()));
    }

    public function stop(): void
    {
        if ($this->pipes !== null) {
            foreach ($this->pipes as $pipe) {
                if (is_resource($pipe)) {
                    fclose($pipe);
                }
            }
            $this->pipes = null;
        }

        if ($this->process !== null) {
            $status = proc_get_status($this->process);
            if ($status['running']) {
                proc_terminate($this->process);
                $deadline = microtime(true) + 1.0;
                while (microtime(true) < $deadline) {
                    $status = proc_get_status($this->process);
                    if (!$status['running']) {
                        break;
                    }
                    usleep(20_000);
                }
                if ($status['running']) {
                    proc_terminate($this->process, 9);
                }
            }
            proc_close($this->process);
            $this->process = null;
        }
    }

    private function isReady(): bool
    {
        $errno = 0;
        $errstr = '';
        $socket = @stream_socket_client(
            sprintf('tcp://%s:%d', $this->host, $this->port),
            $errno,
            $errstr,
            0.2,
        );

        if ($socket === false) {
            return false;
        }

        fclose($socket);

        return true;
    }
}
