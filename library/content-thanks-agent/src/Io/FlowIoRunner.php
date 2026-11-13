<?php

declare(strict_types=1);

namespace App\Io;

use App\Agent\ExecuteAgentJob;
use App\Agent\ThanksAgent;
use App\Instrumentation\CostLedger;
use App\Instrumentation\MetricsSubscriber;
use App\Instrumentation\RunStore;
use App\Instrumentation\ValueRegistry;
use App\Model\AgentId;
use App\Model\AgentPacket;
use App\Agent\CostAgent;
use App\Agent\FetchDocumentationAgent;
use App\Agent\GithubAgent;
use App\Agent\MozillaAgent;
use App\Agent\PHPAgent;
use App\Agent\SummaryAgent;
use Flow\Driver\StreamSelectDriver;
use Flow\ExceptionInterface;
use Flow\FlowFactory;
use Flow\Ip;
use Flow\IpStrategy\MaxIpStrategy;
use Symfony\Component\EventDispatcher\EventDispatcher;

/**
 * Same agent tasks through Flow StreamSelectDriver abstraction.
 */
final class FlowIoRunner
{
    public function __construct(
        private readonly FlowFactory $flowFactory,
        private readonly CostLedger $ledger,
        private readonly RunStore $store,
        private readonly ValueRegistry $values,
        private readonly MetricsSubscriber $metricsSubscriber,
        private readonly string $projectDir,
    ) {}

    public function run(bool $withThanks = true, int $concurrency = 6): BenchResult
    {
        $this->ledger->reset();
        $this->store->reset();

        $driver = new StreamSelectDriver();
        $args = [$driver, $this->ledger, $this->store, $this->values, $this->projectDir];
        $agents = [];
        foreach ([
            new FetchDocumentationAgent(...$args),
            new GithubAgent(...$args),
            new PHPAgent(...$args),
            new MozillaAgent(...$args),
            new CostAgent(...$args),
            new SummaryAgent(...$args),
            new ThanksAgent(...$args),
        ] as $agent) {
            $agents[$agent->id()->value] = $agent;
        }

        $execute = new ExecuteAgentJob($agents);
        $dispatcher = new EventDispatcher();
        $dispatcher->addSubscriber($this->metricsSubscriber);
        $errorJob = static function (ExceptionInterface $e): void {};

        $flow = $this->flowFactory->create(static function () use ($execute, $errorJob, $concurrency, $dispatcher) {
            yield [
                'job' => $execute,
                'errorJob' => $errorJob,
                'ipStrategy' => new MaxIpStrategy($concurrency),
                'dispatcher' => $dispatcher,
            ];
        }, ['driver' => $driver]);

        $ids = AgentId::usefulAgents();
        if ($withThanks) {
            $ids[] = AgentId::Thanks;
        }

        $runId = 'bench-'.bin2hex(random_bytes(3));
        $wallStart = hrtime(true);
        foreach ($ids as $id) {
            $flow(new Ip(new AgentPacket($id, $runId)));
        }
        $flow->await();
        $wall = (hrtime(true) - $wallStart) / 1e6;

        $results = $this->store->all();
        $value = 0;
        $messages = 0;
        $cost = 0.0;
        $busy = 0.0;
        $thanks = 0;
        foreach ($results as $result) {
            $value += $result->value;
            $messages += $result->messages;
            $cost += $result->cost;
            $busy += $result->latencyMs;
            if (AgentId::Thanks === $result->agentId) {
                ++$thanks;
            }
        }
        $snapshot = $this->ledger->snapshot();
        $cost += (float) $snapshot['scheduling']['cost'];

        return new BenchResult(
            runner: 'flow_stream_select',
            wallTimeMs: $wall,
            busyTimeMs: $busy,
            idleTimeMs: max(0, $wall - $busy),
            tasksCompleted: count($results),
            thanksIncluded: $thanks,
            totalCost: $cost,
            totalValue: $value,
            totalMessages: $messages,
            extra: [
                'mux' => 'Flow\\Driver\\StreamSelectDriver',
                'flowEvents' => $snapshot['scheduling'],
                'note' => 'Orchestration guarantees execution, not value.',
            ],
        );
    }
}
