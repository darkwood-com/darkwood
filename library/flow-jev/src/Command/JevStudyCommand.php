<?php

declare(strict_types=1);

namespace App\Command;

use App\AI\Jev\Factory;
use App\AI\Jev\PlatformHandle;
use App\Study\Agent\JevAgentJob;
use App\Study\Agent\RuleAgentJob;
use App\Study\DecidePipeline;
use App\Study\Game\JevGameJob;
use App\Study\Game\RuleGameJob;
use App\Study\JevInvoke;
use App\Study\Payload;
use App\Study\Risk\JevRiskJob;
use App\Study\Risk\RuleRiskJob;
use App\Study\Routing\JevRoutingJob;
use App\Study\Routing\RuleRoutingJob;
use Closure;
use Flow\JobInterface;
use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Style\SymfonyStyle;

#[AsCommand(name: 'app:jev:study', description: 'Run Flow decision-job experiments: rules vs Jev')]
final class JevStudyCommand extends Command
{
    public function __construct(
        private readonly Factory $jevFactory,
    ) {
        parent::__construct();
    }

    protected function configure(): void
    {
        $this
            ->addOption('case', null, InputOption::VALUE_REQUIRED, 'routing|risk|agent|game|all', 'all')
            ->addOption('strategy', null, InputOption::VALUE_REQUIRED, 'rule|jev|both', 'both')
            ->addOption('live', null, InputOption::VALUE_NONE, 'Call real Jev and record cassettes (needs TYPESAFE_API_KEY)');
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $io = new SymfonyStyle($input, $output);
        $case = (string) $input->getOption('case');
        $strategy = (string) $input->getOption('strategy');
        $live = (bool) $input->getOption('live');

        if (!\in_array($case, ['routing', 'risk', 'agent', 'game', 'all'], true)) {
            $io->error('Unknown --case.');

            return Command::INVALID;
        }
        if (!\in_array($strategy, ['rule', 'jev', 'both'], true)) {
            $io->error('Unknown --strategy.');

            return Command::INVALID;
        }

        $cases = 'all' === $case ? ['routing', 'risk', 'agent', 'game'] : [$case];
        foreach ($cases as $name) {
            match ($name) {
                'routing' => $this->routing($io, $strategy, $live),
                'risk' => $this->risk($io, $strategy, $live),
                'agent' => $this->agent($io, $strategy, $live),
                'game' => $this->game($io, $strategy, $live),
            };
        }

        return Command::SUCCESS;
    }

    private function routing(SymfonyStyle $io, string $strategy, bool $live): void
    {
        $io->title('CASE: semantic routing');
        $handle = $this->handle($io, 'routing', $strategy, $live);

        $inputs = [
            'My card was charged twice',
            'The checkout API returns 500 when I save a card',
            'Can I get a quote for the enterprise plan?',
            'I did not make this purchase, lock the account',
            'The invoice looks weird and the app crashed when I paid',
            'Something is wrong with my account',
        ];

        foreach ($inputs as $message) {
            $io->writeln('Input:');
            $io->writeln('  '.$message);
            $this->compare($io, $strategy, $handle, $message, new RuleRoutingJob(), static fn (PlatformHandle $h) => new JevRoutingJob($h->platform, $h->source));
            $io->newLine();
        }

        if (null !== $handle && \in_array($strategy, ['jev', 'both'], true)) {
            $this->threeLevels($io, $handle, 'My card was charged twice');
        }
    }

    private function risk(SymfonyStyle $io, string $strategy, bool $live): void
    {
        $io->title('CASE: risk / fraud (toy, not production)');
        $handle = $this->handle($io, 'risk', $strategy, $live);

        $inputs = [
            ['amount' => 24.5, 'country' => 'FR', 'device' => 'known-laptop', 'previous' => '12 ok payments', 'unusual' => false],
            ['amount' => 1800, 'country' => 'FR', 'device' => 'known-laptop', 'previous' => '12 ok payments', 'unusual' => false],
            ['amount' => 80, 'country' => 'NG', 'device' => 'new-phone', 'previous' => 'none', 'unusual' => false],
            ['amount' => 9000, 'country' => 'XX', 'device' => 'new-phone', 'previous' => 'none', 'unusual' => true],
            ['amount' => 400, 'country' => 'US', 'device' => 'known-laptop', 'previous' => '1 chargeback last week', 'unusual' => true],
        ];

        foreach ($inputs as $tx) {
            $io->writeln('Input:');
            $io->writeln('  '.json_encode($tx, \JSON_UNESCAPED_SLASHES));
            $this->compare($io, $strategy, $handle, $tx, new RuleRiskJob(), static fn (PlatformHandle $h) => new JevRiskJob($h->platform, $h->source));
            $io->newLine();
        }
    }

    private function agent(SymfonyStyle $io, string $strategy, bool $live): void
    {
        $io->title('CASE: agent continuation (fake agent, Jev/rules decide next action)');
        $handle = $this->handle($io, 'agent', $strategy, $live);

        $inputs = [
            ['objective' => 'Fetch 3 catalog pages', 'step' => 1, 'last_result' => 'ok', 'remaining' => 2, 'retries' => 0, 'note' => 'page 1 stored'],
            ['objective' => 'Fetch 3 catalog pages', 'step' => 2, 'last_result' => 'error', 'remaining' => 2, 'retries' => 0, 'note' => 'timeout on page 2'],
            ['objective' => 'Fetch 3 catalog pages', 'step' => 2, 'last_result' => 'error', 'remaining' => 2, 'retries' => 2, 'note' => 'timeout again'],
            ['objective' => 'Fetch 3 catalog pages', 'step' => 3, 'last_result' => 'ok', 'remaining' => 0, 'retries' => 0, 'note' => 'all pages stored'],
        ];

        foreach ($inputs as $state) {
            $io->writeln('Input:');
            $io->writeln('  '.json_encode($state, \JSON_UNESCAPED_SLASHES));
            $this->compare($io, $strategy, $handle, $state, new RuleAgentJob(), static fn (PlatformHandle $h) => new JevAgentJob($h->platform, $h->source));
            $io->newLine();
        }
    }

    private function game(SymfonyStyle $io, string $strategy, bool $live): void
    {
        $io->title('CASE: game decision (tiny hunt grid; conceptual Battleship only)');
        $io->note('External inspiration (not copied): https://github.com/ickas/battleship-vs-jev — Jev may match plain code rather than beat it.');
        $handle = $this->handle($io, 'game', $strategy, $live);

        $inputs = [
            ['size' => 5, 'shots' => []],
            ['size' => 5, 'shots' => ['A1', 'C1', 'E1', 'B2']],
            ['size' => 5, 'shots' => ['A1', 'C1', 'E1', 'B2', 'D2', 'A3', 'C3']],
        ];

        foreach ($inputs as $board) {
            $io->writeln('Input:');
            $io->writeln('  '.json_encode($board, \JSON_UNESCAPED_SLASHES));
            $this->compare($io, $strategy, $handle, $board, new RuleGameJob(), static fn (PlatformHandle $h) => new JevGameJob($h->platform, $h->source));
            $io->newLine();
        }
    }

    /**
     * @param Closure(PlatformHandle): JobInterface $jevJob
     */
    private function compare(
        SymfonyStyle $io,
        string $strategy,
        ?PlatformHandle $handle,
        mixed $input,
        JobInterface $ruleJob,
        Closure $jevJob,
    ): void {
        $rule = null;
        $jev = null;

        if (\in_array($strategy, ['rule', 'both'], true)) {
            $rule = DecidePipeline::run($input, $ruleJob);
            $this->printDecision($io, 'Rule job', $rule);
        }

        if (\in_array($strategy, ['jev', 'both'], true)) {
            if (null === $handle) {
                $io->writeln('Jev job:');
                $io->writeln('  source: blocked');
                $io->writeln('  error: no TYPESAFE_API_KEY and no cassette; not fabricating a Jev answer');
            } else {
                $jev = DecidePipeline::run($input, $jevJob($handle));
                $this->printDecision($io, 'Jev job', $jev);
            }
        }

        if (null !== $rule && null !== $jev) {
            $agree = $rule->decision === $jev->decision && null !== $jev->decision;
            $io->writeln('Agreement: '.($agree ? 'yes' : 'no'));
        }
    }

    private function printDecision(SymfonyStyle $io, string $label, Payload $payload): void
    {
        $io->writeln($label.':');
        $io->writeln('  job: '.$payload->job);
        $io->writeln('  decision: '.($payload->decision ?? '(none)'));
        if (null !== $payload->confidence) {
            $io->writeln(\sprintf('  confidence: %.3f', $payload->confidence));
        }
        if ([] !== $payload->alternatives) {
            $alts = [];
            foreach ($payload->alternatives as $name => $p) {
                $alts[] = \sprintf('%s=%.3f', $name, $p);
            }
            $io->writeln('  alternatives: '.implode(', ', $alts));
        }
        if (isset($payload->answers['is_urgent']['noul'])) {
            $io->writeln(\sprintf('  noul is_urgent: %.3f', (float) $payload->answers['is_urgent']['noul']));
        }
        if (isset($payload->answers['risk']['score'])) {
            $io->writeln(\sprintf('  score risk: %.3f', (float) $payload->answers['risk']['score']));
        }
        $io->writeln(\sprintf('  latency_ms: %.2f', $payload->latencyMs));
        $io->writeln('  source: '.$payload->source);
        if (null !== $payload->error) {
            $io->writeln('  error: '.$payload->error);
        }
    }

    private function threeLevels(SymfonyStyle $io, PlatformHandle $handle, string $message): void
    {
        $io->section('Three levels on one input (routing)');
        $io->writeln('Input: '.$message);

        $aStart = hrtime(true);
        $a = (new RuleRoutingJob())(Payload::fromInput($message));
        $aMs = (hrtime(true) - $aStart) / 1_000_000;
        $io->writeln(\sprintf('A. PHP rules (no Flow, no Jev): decision=%s latency_ms=%.2f', $a->decision, $aMs));

        $bStart = hrtime(true);
        try {
            $direct = JevInvoke::call($handle->platform, $message, JevRoutingJob::questions());
            $bMs = (hrtime(true) - $bStart) / 1_000_000;
            $io->writeln(\sprintf(
                'B. Platform → Jev (no Flow): decision=%s confidence=%s latency_ms=%.2f source=%s',
                $direct->getChoice('department') ?? '(none)',
                null !== $direct->getConfidence('department') ? \sprintf('%.3f', $direct->getConfidence('department')) : 'n/a',
                $bMs,
                $handle->source,
            ));
        } catch (\Throwable $e) {
            $bMs = (hrtime(true) - $bStart) / 1_000_000;
            $io->writeln(\sprintf('B. Platform → Jev (no Flow): error=%s latency_ms=%.2f', $e->getMessage(), $bMs));
        }

        $cStart = hrtime(true);
        $c = DecidePipeline::run($message, new JevRoutingJob($handle->platform, $handle->source));
        $cMs = (hrtime(true) - $cStart) / 1_000_000;
        $io->writeln(\sprintf(
            'C. Flow → JevRoutingJob → Platform → Jev: decision=%s confidence=%s latency_ms=%.2f source=%s',
            $c->decision ?? '(none)',
            null !== $c->confidence ? \sprintf('%.3f', $c->confidence) : 'n/a',
            $cMs,
            $c->source,
        ));
        $io->writeln('Flow cost vs B is orchestration, not inference. C is not assumed better.');
    }

    private function handle(SymfonyStyle $io, string $cassette, string $strategy, bool $live): ?PlatformHandle
    {
        if (!\in_array($strategy, ['jev', 'both'], true)) {
            return null;
        }

        $handle = $this->jevFactory->tryCreate($cassette, $live);
        if (null === $handle) {
            $why = $live
                ? 'TYPESAFE_API_KEY is empty; live Jev is blocked'
                : 'no cassette at '.$this->jevFactory->cassettePath($cassette).' and not --live';
            $io->warning('Jev '.$why.'. Rule jobs still run. Answers will not be invented.');

            return null;
        }

        $io->text(\sprintf('Jev source=%s cassette=%s', $handle->source, $handle->cassettePath));

        return $handle;
    }
}
