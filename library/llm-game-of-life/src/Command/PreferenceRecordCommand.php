<?php

declare(strict_types=1);

namespace App\Command;

use App\Application\Preference\PairwisePreferenceService;
use App\Domain\LambdaLanguage\Program;
use App\Domain\LambdaLanguage\ProgramValidator;
use App\Infrastructure\Preference\JsonPreferenceRepository;
use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Style\SymfonyStyle;

#[AsCommand(
    name: 'app:preferences:record',
    description: 'Record a pairwise program preference as JSON.',
)]
final class PreferenceRecordCommand extends Command
{
    public function __construct(
        private readonly PairwisePreferenceService $preferences,
        private readonly JsonPreferenceRepository $repository,
    ) {
        parent::__construct();
    }

    protected function configure(): void
    {
        $this
            ->addOption('preferred-ast-file', null, InputOption::VALUE_REQUIRED, 'Path to the preferred program AST JSON.')
            ->addOption('rejected-ast-file', null, InputOption::VALUE_REQUIRED, 'Path to the rejected program AST JSON.')
            ->addOption('preferred-score', null, InputOption::VALUE_REQUIRED, 'Scalar score for the preferred program.')
            ->addOption('rejected-score', null, InputOption::VALUE_REQUIRED, 'Scalar score for the rejected program.')
            ->addOption('mode', null, InputOption::VALUE_REQUIRED, 'Preference context mode.', 'manual')
            ->addOption('dataset', null, InputOption::VALUE_REQUIRED, 'Optional supervised dataset context.')
            ->addOption('seed', null, InputOption::VALUE_REQUIRED, 'Optional seed context.')
            ->addOption('size', null, InputOption::VALUE_REQUIRED, 'Grid size for AST validation.', '16')
            ->addOption('id', null, InputOption::VALUE_REQUIRED, 'Optional stable preference id.');
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $io = new SymfonyStyle($input, $output);

        try {
            $size = $this->readPositiveInt((string) $input->getOption('size'), 'size');
            $preferredAst = $this->readAstFile($input->getOption('preferred-ast-file'));
            $rejectedAst = $this->readAstFile($input->getOption('rejected-ast-file'));

            Program::fromArray($preferredAst, new ProgramValidator(gridSize: $size));
            Program::fromArray($rejectedAst, new ProgramValidator(gridSize: $size));

            $record = $this->preferences->record(
                $preferredAst,
                $rejectedAst,
                $this->readFloat($input->getOption('preferred-score'), 'preferred-score'),
                $this->readFloat($input->getOption('rejected-score'), 'rejected-score'),
                $this->context($input, $size),
                $this->optionalString($input->getOption('id')),
            );

            $path = $this->repository->save($record);
            $record['path'] = $path;
            $output->writeln(json_encode($record, JSON_THROW_ON_ERROR));
        } catch (\Throwable $exception) {
            $io->error($exception->getMessage());

            return Command::FAILURE;
        }

        return Command::SUCCESS;
    }

    /**
     * @return array<string,mixed>
     */
    private function context(InputInterface $input, int $size): array
    {
        $context = [
            'mode' => (string) $input->getOption('mode'),
            'size' => $size,
        ];

        $dataset = $this->optionalString($input->getOption('dataset'));
        if ($dataset !== null) {
            $context['dataset'] = $dataset;
        }

        $seed = $this->optionalString($input->getOption('seed'));
        if ($seed !== null) {
            $context['seed'] = $this->readPositiveInt($seed, 'seed');
        }

        return $context;
    }

    /**
     * @return array<string,mixed>
     */
    private function readAstFile(mixed $path): array
    {
        $path = $this->optionalString($path);
        if ($path === null) {
            throw new \InvalidArgumentException('Both --preferred-ast-file and --rejected-ast-file are required.');
        }

        if (!is_file($path)) {
            throw new \InvalidArgumentException(sprintf('AST file "%s" does not exist.', $path));
        }

        $decoded = json_decode((string) file_get_contents($path), true, 512, JSON_THROW_ON_ERROR);
        if (!\is_array($decoded)) {
            throw new \InvalidArgumentException('AST JSON must decode to an object.');
        }

        return $decoded;
    }

    private function readFloat(mixed $raw, string $name): float
    {
        if (!\is_string($raw) || trim($raw) === '' || !is_numeric($raw)) {
            throw new \InvalidArgumentException(sprintf('Option --%s must be numeric.', $name));
        }

        return (float) $raw;
    }

    private function readPositiveInt(string $raw, string $name): int
    {
        if (!ctype_digit($raw) || (int) $raw <= 0) {
            throw new \InvalidArgumentException(sprintf('Option --%s must be a positive integer.', $name));
        }

        return (int) $raw;
    }

    private function optionalString(mixed $raw): ?string
    {
        if (!\is_string($raw) || trim($raw) === '') {
            return null;
        }

        return $raw;
    }
}
