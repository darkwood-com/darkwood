<?php

declare(strict_types=1);

namespace App\Orchestration;

use App\Agent\CostAgent;
use App\Agent\ExecuteAgentJob;
use App\Agent\FetchDocumentationAgent;
use App\Agent\GithubAgent;
use App\Agent\MozillaAgent;
use App\Agent\PHPAgent;
use App\Agent\SummaryAgent;
use App\Agent\ThanksAgent;
use App\Instrumentation\CostLedger;
use App\Instrumentation\MetricsSubscriber;
use App\Instrumentation\RunStore;
use App\Instrumentation\Scoreboard;
use App\Instrumentation\ValueRegistry;
use App\Model\AgentId;
use App\Model\AgentPacket;
use App\Model\RunReport;
use Flow\Driver\FiberDriver;
use Flow\Driver\StreamSelectDriver;
use Flow\DriverInterface;
use Flow\ExceptionInterface;
use Flow\FlowFactory;
use Flow\Ip;
use Flow\IpStrategy\MaxIpStrategy;
use Symfony\Component\EventDispatcher\EventDispatcher;

final class AgentPipelineFactory
{
    public function __construct(
        private readonly FlowFactory $flowFactory,
        private readonly CostLedger $ledger,
        private readonly RunStore $store,
        private readonly ValueRegistry $values,
        private readonly MetricsSubscriber $metricsSubscriber,
        private readonly Scoreboard $scoreboard,
        private readonly string $projectDir,
    ) {}

    public function compare(bool $withThanks, string $driverName = 'fiber', int $concurrency = 6): RunReport
    {
        $this->ledger->reset();
        $this->store->reset();

        $driver = $this->createDriver($driverName);
        $agents = $this->createAgents($driver);
        $execute = new ExecuteAgentJob($agents);

        $dispatcher = new EventDispatcher();
        $dispatcher->addSubscriber($this->metricsSubscriber);

        $errorJob = static function (ExceptionInterface $exception): void {
            // Errors are metered in agents / ledger; keep pipeline alive.
        };

        $flow = $this->flowFactory->create(static function () use ($execute, $errorJob, $concurrency, $dispatcher) {
            yield [
                'job' => $execute,
                'errorJob' => $errorJob,
                'ipStrategy' => new MaxIpStrategy($concurrency),
                'dispatcher' => $dispatcher,
            ];
        }, ['driver' => $driver]);

        $runId = bin2hex(random_bytes(4));
        $agentIds = AgentId::usefulAgents();
        if ($withThanks) {
            $agentIds[] = AgentId::Thanks;
        }

        $wallStart = hrtime(true);
        foreach ($agentIds as $agentId) {
            $flow(new Ip(new AgentPacket($agentId, $runId)));
        }
        $flow->await();

        // Fan-in: SummaryAgent sees gathered RunStore results (must be sync — outside driver loop).
        $summaryAgent = $agents[AgentId::Summary->value];
        $summaryAgent->executeSync(new AgentPacket(AgentId::Summary, $runId));

        $wallMs = (hrtime(true) - $wallStart) / 1e6;

        return $this->scoreboard->buildReport(
            runId: $runId,
            withThanks: $withThanks,
            driver: $driver::class,
            wallTimeMs: $wallMs,
            results: $this->store->all(),
            ledger: $this->ledger,
        );
    }

    /**
     * @return array<string, \App\Agent\AbstractAgent>
     */
    private function createAgents(DriverInterface $driver): array
    {
        $args = [$driver, $this->ledger, $this->store, $this->values, $this->projectDir];

        $list = [
            new FetchDocumentationAgent(...$args),
            new GithubAgent(...$args),
            new PHPAgent(...$args),
            new MozillaAgent(...$args),
            new CostAgent(...$args),
            new SummaryAgent(...$args),
            new ThanksAgent(...$args),
        ];

        $map = [];
        foreach ($list as $agent) {
            $map[$agent->id()->value] = $agent;
        }

        return $map;
    }

    private function createDriver(string $name): DriverInterface
    {
        return match (strtolower($name)) {
            'stream', 'streamselect', 'stream_select' => new StreamSelectDriver(),
            default => new FiberDriver(),
        };
    }
}
