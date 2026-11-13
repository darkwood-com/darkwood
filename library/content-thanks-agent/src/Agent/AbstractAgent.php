<?php

declare(strict_types=1);

namespace App\Agent;

use App\Instrumentation\CostLedger;
use App\Instrumentation\RunStore;
use App\Instrumentation\ValueRegistry;
use App\Model\AgentId;
use App\Model\AgentPacket;
use App\Model\AgentResult;
use Flow\Driver\StreamSelectDriver;
use Flow\DriverInterface;
use Flow\JobInterface;
use Generator;

/**
 * @implements JobInterface<AgentPacket, AgentResult|Generator>
 */
abstract class AbstractAgent implements JobInterface
{
    public function __construct(
        protected readonly DriverInterface $driver,
        protected readonly CostLedger $ledger,
        protected readonly RunStore $store,
        protected readonly ValueRegistry $values,
        protected readonly string $projectDir,
    ) {}

    abstract public function id(): AgentId;

    /**
     * @return array{output: string, tokens: int, messages: int, didWork: bool, payload?: array<string, mixed>}
     */
    abstract protected function produce(AgentPacket $packet): array;

    public function __invoke($data): mixed
    {
        assert($data instanceof AgentPacket);

        if ($this->driver instanceof StreamSelectDriver) {
            return $this->runAsGenerator($data);
        }

        return $this->executeSync($data);
    }

    /**
     * Fan-in / post-await entry point — never returns a Generator.
     */
    public function executeSync(AgentPacket $packet): AgentResult
    {
        return $this->runBlocking($packet);
    }

    private function runBlocking(AgentPacket $packet): AgentResult
    {
        $start = hrtime(true);

        if ($packet->simulateError) {
            $this->simulateIoDelay(0.05);
            $tokens = 32;
            $cost = CostLedger::tokenCost($tokens);
            $result = new AgentResult(
                agentId: $this->id(),
                runId: $packet->runId,
                output: '',
                value: 0,
                latencyMs: (hrtime(true) - $start) / 1e6,
                messages: 1,
                tokens: $tokens,
                cost: $cost,
                didWork: false,
                payload: [],
                success: false,
                error: 'Simulated max-output / stream error (metered on failure path)',
            );
            $this->ledger->recordResult($result);
            $this->ledger->recordTerminalError($this->id(), $tokens, $cost);
            $this->store->put($result);

            return $result;
        }

        $this->simulateIoDelay($this->ioSeconds());
        $produced = $this->produce($packet);
        $latencyMs = (hrtime(true) - $start) / 1e6;
        $tokens = $produced['tokens'];
        $messages = $produced['messages'];
        $cost = CostLedger::tokenCost($tokens) + ($messages * 0.0003);
        // Scheduling/context tax even for ThanksAgent
        $cost += 0.0005;

        $result = new AgentResult(
            agentId: $this->id(),
            runId: $packet->runId,
            output: $produced['output'],
            value: $produced['didWork'] ? $this->values->valueFor($this->id()) : 0,
            latencyMs: $latencyMs,
            messages: $messages,
            tokens: $tokens,
            cost: $cost,
            didWork: $produced['didWork'],
            payload: $produced['payload'] ?? [],
        );

        $this->ledger->recordResult($result);
        $this->store->put($result);

        return $result;
    }

    private function runAsGenerator(AgentPacket $packet): Generator
    {
        $start = hrtime(true);
        /** @var StreamSelectDriver $driver */
        $driver = $this->driver;

        $stream = $this->openDelayedReadable($this->ioSeconds());
        try {
            yield $driver->waitReadable($stream, 5.0);
            stream_get_contents($stream);
            // Simulated I/O latency still goes through the mux scheduler.
            yield $driver->waitDelay($this->ioSeconds());
        } finally {
            if (is_resource($stream)) {
                fclose($stream);
            }
        }

        if ($packet->simulateError) {
            $tokens = 32;
            $cost = CostLedger::tokenCost($tokens);
            $result = new AgentResult(
                agentId: $this->id(),
                runId: $packet->runId,
                output: '',
                value: 0,
                latencyMs: (hrtime(true) - $start) / 1e6,
                messages: 1,
                tokens: $tokens,
                cost: $cost,
                didWork: false,
                success: false,
                error: 'Simulated max-output / stream error (metered on failure path)',
            );
            $this->ledger->recordResult($result);
            $this->ledger->recordTerminalError($this->id(), $tokens, $cost);
            $this->store->put($result);

            return $result;
        }

        $produced = $this->produce($packet);
        $latencyMs = (hrtime(true) - $start) / 1e6;
        $tokens = $produced['tokens'];
        $messages = $produced['messages'];
        $cost = CostLedger::tokenCost($tokens) + ($messages * 0.0003) + 0.0005;

        $result = new AgentResult(
            agentId: $this->id(),
            runId: $packet->runId,
            output: $produced['output'],
            value: $produced['didWork'] ? $this->values->valueFor($this->id()) : 0,
            latencyMs: $latencyMs,
            messages: $messages,
            tokens: $tokens,
            cost: $cost,
            didWork: $produced['didWork'],
            payload: $produced['payload'] ?? [],
        );

        $this->ledger->recordResult($result);
        $this->store->put($result);

        return $result;
    }

    protected function ioSeconds(): float
    {
        return 0.08;
    }

    protected function simulateIoDelay(float $seconds): void
    {
        $seconds = max(0.01, $seconds);
        if (null !== \Fiber::getCurrent()) {
            $this->driver->delay($seconds);

            return;
        }

        usleep((int) ($seconds * 1e6));
    }

    /**
     * @return resource
     */
    protected function openDelayedReadable(float $seconds)
    {
        $sockets = stream_socket_pair(STREAM_PF_UNIX, STREAM_SOCK_STREAM, STREAM_IPPROTO_IP);
        if (false === $sockets) {
            throw new \RuntimeException('Unable to create stream_socket_pair for I/O demo.');
        }

        [$read, $write] = $sockets;
        stream_set_blocking($read, false);
        stream_set_blocking($write, false);

        // Write after delay in a non-blocking fashion via register — for StreamSelect we
        // write immediately a marker and rely on waitDelay for the simulated latency.
        fwrite($write, 'x');
        fclose($write);

        return $read;
    }

    protected function readFixture(string $name): string
    {
        $path = $this->projectDir.'/fixtures/sources/'.$name;
        if (!is_file($path)) {
            return sprintf('[missing fixture: %s]', $name);
        }

        return (string) file_get_contents($path);
    }

    protected function estimateTokens(string $text): int
    {
        return max(1, (int) ceil(strlen($text) / 4));
    }
}
