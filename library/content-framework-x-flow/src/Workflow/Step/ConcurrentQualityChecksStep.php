<?php

declare(strict_types=1);

namespace App\Workflow\Step;

use App\Workflow\ChangeReviewContext;
use React\EventLoop\Loop;
use React\Promise\Deferred;
use React\Promise\PromiseInterface;
use RuntimeException;

use function React\Async\await;
use function React\Promise\all;

/**
 * Adapter: runs tests / static-analysis / security-review concurrently via ReactPHP
 * promises inside one Flow job. Not a native Flow parallel-step primitive.
 */
final class ConcurrentQualityChecksStep
{
    public function __invoke(ChangeReviewContext $ctx): ChangeReviewContext
    {
        $t = $ctx->beginStep('quality-checks-concurrent');

        if (($ctx->input['fail'] ?? null) === 'promise') {
            throw new RuntimeException('Controlled rejected-promise style failure (fail=promise)');
        }

        $checks = [
            'tests' => 0.04,
            'static-analysis' => 0.05,
            'security-review' => 0.06,
        ];

        /** @var array<string, PromiseInterface<array<string, mixed>>> $promises */
        $promises = [];
        foreach ($checks as $name => $seconds) {
            $promises[$name] = $this->delayedCheck($name, $seconds, $ctx->input['fail'] ?? null);
        }

        /** @var array<string, array<string, mixed>> $outputs */
        $outputs = await(all($promises));

        foreach ($outputs as $name => $output) {
            if (($output['status'] ?? '') === 'failed') {
                $ctx->fail('quality_check_failed', (string) ($output['message'] ?? "Check {$name} failed"));
                $ctx->warnings[] = "Quality check failed: {$name}";
            }
        }

        $ctx->result['quality'] = [
            'mode' => 'concurrent-adapter',
            'checks' => $outputs,
        ];
        $ctx->completeStep('quality-checks-concurrent', $t, [
            'mode' => 'concurrent-adapter',
            'checks' => $outputs,
        ], $ctx->status === 'failed' ? 'failed' : 'completed');

        return $ctx;
    }

    /**
     * @return PromiseInterface<array<string, mixed>>
     */
    private function delayedCheck(string $name, float $seconds, mixed $fail): PromiseInterface
    {
        $deferred = new Deferred();
        Loop::addTimer($seconds, function () use ($deferred, $name, $fail): void {
            try {
                $deferred->resolve($this->evaluate($name, $fail));
            } catch (\Throwable $e) {
                $deferred->reject($e);
            }
        });

        return $deferred->promise();
    }

    /**
     * @return array<string, mixed>
     */
    private function evaluate(string $name, mixed $fail): array
    {
        if ($fail === 'security' && $name === 'security-review') {
            return [
                'status' => 'failed',
                'message' => 'Security check failed: no dependency audit result was supplied.',
            ];
        }

        if ($fail === 'async' && $name === 'tests') {
            throw new RuntimeException('Controlled asynchronous failure during tests check (fail=async)');
        }

        return [
            'status' => 'passed',
            'message' => "Deterministic {$name} check passed.",
        ];
    }
}
