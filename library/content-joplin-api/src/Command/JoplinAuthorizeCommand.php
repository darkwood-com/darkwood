<?php

declare(strict_types=1);

namespace App\Command;

use App\Service\JoplinService;
use Exception;
use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Style\SymfonyStyle;

#[AsCommand(
    name: 'app:joplin:authorize',
    description: 'Authorize the application to access Joplin',
)]
class JoplinAuthorizeCommand extends Command
{
    public function __construct(
        private JoplinService $joplinService
    ) {
        parent::__construct();
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $io = new SymfonyStyle($input, $output);

        try {
            $io->info('Requesting authorization from Joplin...');
            $authToken = $this->joplinService->requestAuthorization();

            $io->warning([
                'Please check your Joplin desktop application.',
                'You should see a prompt asking to authorize this application.',
                'Please accept the request in Joplin.',
            ]);

            $io->info('Waiting for authorization...');
            $token = $this->joplinService->waitForAuthorization($authToken);

            $io->success([
                'Authorization successful!',
                'Please add this token to your .env file:',
                'JOPLIN_TOKEN=' . $token,
            ]);

            return Command::SUCCESS;
        } catch (Exception $e) {
            $io->error($e->getMessage());

            return Command::FAILURE;
        }
    }
}
