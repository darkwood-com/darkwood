<?php

declare(strict_types=1);

namespace App\Command;

use App\Token\ContextStore;
use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Style\SymfonyStyle;

#[AsCommand(
    name: 'tokens:show-ref',
    description: 'Retrieve original content stored under a #ref / ctx_ id',
)]
final class TokensShowRefCommand extends Command
{
    public function __construct(
        private readonly ContextStore $store,
    ) {
        parent::__construct();
    }

    protected function configure(): void
    {
        $this->addArgument('id', InputArgument::REQUIRED, 'Reference id (ctx_xxxxxx or #ref:ctx_xxxxxx)');
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $io = new SymfonyStyle($input, $output);
        $id = (string) $input->getArgument('id');
        $ref = $this->store->get($id);

        if ($ref === null) {
            $io->error(sprintf('Reference not found: %s', $id));

            return Command::FAILURE;
        }

        $io->writeln(sprintf('Source: %s', $ref->source));
        $io->writeln(sprintf('Type: %s', $ref->type));
        $io->writeln(sprintf('Reason: %s', $ref->reason));
        $io->writeln(sprintf('Id: %s', $ref->id));
        $io->newLine();
        $io->writeln('--- RAW CONTENT ---');
        $io->newLine();
        $io->writeln($ref->content);

        return Command::SUCCESS;
    }
}
