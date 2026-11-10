<?php

declare(strict_types=1);

namespace App\Command;

use App\Orchestration\AgentPipelineFactory;
use App\Instrumentation\Scoreboard;
use App\Model\AgentId;
use App\Model\AgentPacket;
use App\Agent\ExecuteAgentJob;
use App\Agent\FetchDocumentationAgent;
use App\Agent\GithubAgent;
use App\Agent\PHPAgent;
use App\Agent\MozillaAgent;
use App\Agent\CostAgent;
use App\Agent\SummaryAgent;
use App\Agent\ThanksAgent;
use App\Instrumentation\CostLedger;
use App\Instrumentation\MetricsSubscriber;
use App\Instrumentation\RunStore;
use App\Instrumentation\ValueRegistry;
use Flow\Driver\FiberDriver;
use Flow\ExceptionInterface;
use Flow\FlowFactory;
use Flow\Ip;
use Flow\IpStrategy\MaxIpStrategy;
use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Style\SymfonyStyle;
use Symfony\Component\EventDispatcher\EventDispatcher;

#[AsCommand(name: 'thanks-agent:run', description: 'Run the multi-agent Flow pipeline once')]
final class RunCommand extends Command
{
    public function __construct(
        private readonly AgentPipelineFactory $pipeline,
        private readonly Scoreboard $scoreboard,
        private readonly CostLedger $ledger,
        private readonly RunStore $store,
        private readonly ValueRegistry $values,
        private readonly MetricsSubscriber $metricsSubscriber,
        private readonly FlowFactory $flowFactory,
        private readonly string $projectDir,
    ) {
        parent::__construct();
    }

    protected function configure(): void
    {
        $this
            ->addOption('with-thanks', null, InputOption::VALUE_NONE, 'Include ThanksAgent')
            ->addOption('simulate-error', null, InputOption::VALUE_NONE, 'Force metered failure on CostAgent (PR #2363 lesson)')
            ->addOption('save', null, InputOption::VALUE_NONE, 'Write JSON report');
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $io = new SymfonyStyle($input, $output);
        $withThanks = (bool) $input->getOption('with-thanks');

        if ($input->getOption('simulate-error')) {
            return $this->runWithErrorMetering($io, $withThanks, (bool) $input->getOption('save'));
        }

        $report = $this->pipeline->compare($withThanks, 'fiber', 6);
        $this->scoreboard->render($io, $report);

        if ($input->getOption('save')) {
            $dir = $this->projectDir.'/var/reports';
            if (!is_dir($dir)) {
                mkdir($dir, 0775, true);
            }
            $path = $dir.'/run-'.date('Ymd-His').'.json';
            file_put_contents($path, json_encode($report->toArray(), JSON_PRETTY_PRINT));
            $io->success('Saved '.$path);
        }

        return Command::SUCCESS;
    }

    private function runWithErrorMetering(SymfonyStyle $io, bool $withThanks, bool $save): int
    {
        $this->ledger->reset();
        $this->store->reset();
        $driver = new FiberDriver();
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
        $flow = $this->flowFactory->create(static function () use ($execute, $dispatcher) {
            yield [
                'job' => $execute,
                'errorJob' => static function (ExceptionInterface $e): void {},
                'ipStrategy' => new MaxIpStrategy(6),
                'dispatcher' => $dispatcher,
            ];
        }, ['driver' => $driver]);

        $runId = 'err-'.bin2hex(random_bytes(3));
        $ids = AgentId::usefulAgents();
        if ($withThanks) {
            $ids[] = AgentId::Thanks;
        }

        $wallStart = hrtime(true);
        foreach ($ids as $id) {
            $simulate = AgentId::Cost === $id;
            $flow(new Ip(new AgentPacket($id, $runId, simulateError: $simulate)));
        }
        $flow->await();

        $summaryAgent = $agents[AgentId::Summary->value];
        $summaryAgent->executeSync(new AgentPacket(AgentId::Summary, $runId));

        $wall = (hrtime(true) - $wallStart) / 1e6;

        $report = $this->scoreboard->buildReport(
            $runId,
            $withThanks,
            $driver::class,
            $wall,
            $this->store->all(),
            $this->ledger,
        );
        $this->scoreboard->render($io, $report);
        $io->note('CostAgent failure was metered on the error terminal — happy-path-only FinOps would have lied.');

        if ($save) {
            $dir = $this->projectDir.'/var/reports';
            if (!is_dir($dir)) {
                mkdir($dir, 0775, true);
            }
            file_put_contents(
                $dir.'/run-error-metering-'.date('Ymd-His').'.json',
                json_encode($report->toArray(), JSON_PRETTY_PRINT),
            );
        }

        return Command::SUCCESS;
    }
}
