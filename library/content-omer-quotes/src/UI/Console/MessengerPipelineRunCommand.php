<?php
declare(strict_types=1);
namespace App\UI\Console;
use App\Infrastructure\Orchestration\Messenger\Message\RunMessengerPipeline;
use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Messenger\MessageBusInterface;

#[AsCommand(name: 'app:pipeline:messenger:run')]
final class MessengerPipelineRunCommand extends Command
{
    public function __construct(private readonly MessageBusInterface $bus) { parent::__construct(); }
    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $this->bus->dispatch(new RunMessengerPipeline(uniqid('msg_', true)));
        $output->writeln('Messenger pipeline complete');
        return Command::SUCCESS;
    }
}
