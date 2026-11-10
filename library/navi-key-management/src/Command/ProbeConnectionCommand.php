<?php

declare(strict_types=1);

namespace App\Command;

use App\AiCostTable;
use App\CredentialProtector;
use App\Entity\AiConnection;
use App\Navi\TestAiConnectionAction;
use Navi\Application\Workflow\WorkflowRunner;
use Navi\Domain\Execution\Context;
use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Contracts\HttpClient\HttpClientInterface;

#[AsCommand(name: 'app:probe-connection', description: 'Encrypt a key from an env var, run Navi, print usage — never the key')]
final class ProbeConnectionCommand extends Command
{
    public function __construct(
        private CredentialProtector $credentials,
        private WorkflowRunner $workflows,
        private HttpClientInterface $httpClient,
        private AiCostTable $costTable,
    ) {
        parent::__construct();
    }

    protected function configure(): void
    {
        $this->addOption('env-name', null, InputOption::VALUE_REQUIRED, 'Env var holding the API key', 'ANTHROPIC_API_KEY');
        $this->addOption('provider', null, InputOption::VALUE_REQUIRED, 'Provider', 'anthropic');
        $this->addOption('model', null, InputOption::VALUE_REQUIRED, 'Model', 'claude-sonnet-4-5');
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $envName = (string) $input->getOption('env-name');
        $apiKey = $_ENV[$envName] ?? $_SERVER[$envName] ?? getenv($envName);
        if (!\is_string($apiKey) || $apiKey === '') {
            $output->writeln('No credential in the named environment variable. Skipping.');

            return Command::SUCCESS;
        }

        $connection = new AiConnection();
        $connection->setLabel('probe');
        $connection->setProvider((string) $input->getOption('provider'));
        $connection->setModel((string) $input->getOption('model'));
        $this->credentials->persist($connection, $this->credentials->encrypt($apiKey));

        $output->writeln('plaintext_in_ciphertext: '.(str_contains($connection->getCiphertextBlob(), $apiKey) ? 'YES' : 'no'));

        $decrypted = $this->credentials->decrypt($connection);
        $output->writeln('decrypt_matches: '.(hash_equals($apiKey, $decrypted) ? 'yes' : 'no'));

        $result = $this->workflows->run(
            Context::fromArray([
                'provider' => $connection->getProvider(),
                'model' => $connection->getModel(),
            ]),
            [
                new TestAiConnectionAction(
                    $decrypted,
                    $connection->getProvider(),
                    $connection->getModel(),
                    null,
                    $this->httpClient,
                    $this->costTable,
                ),
            ],
        );

        $context = $result->state()->context()->toArray();
        $encoded = json_encode($context, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES);
        $output->writeln('context_has_secret: '.(str_contains((string) $encoded, $apiKey) ? 'YES' : 'no'));
        $output->writeln((string) $encoded);

        return Command::SUCCESS;
    }
}
