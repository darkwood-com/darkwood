<?php

namespace App\Command;

use App\Capability\ComposioToolChain;
use App\Toolbox\Toolbox;
use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Style\SymfonyStyle;
use Symfony\AI\Agent\Agent;
use Symfony\AI\Agent\Toolbox\AgentProcessor;
use Symfony\AI\Agent\Toolbox\Tool\Wikipedia;
use Symfony\AI\Agent\Toolbox\ToolFactory\MemoryToolFactory;
use Symfony\AI\Platform\Bridge\Anthropic\Claude;
use Symfony\AI\Platform\Bridge\Anthropic\PlatformFactory;
use Symfony\AI\Platform\Message\Message;
use Symfony\AI\Platform\Message\MessageBag;
use Symfony\Component\Dotenv\Dotenv;
use Symfony\Component\HttpClient\HttpClient;
use Flow\Flow\Flow;
use Flow\FlowFactory;
use Flow\Ip;

#[AsCommand(
    name: 'app:composio',
    description: 'Add a short description for your command',
)]
class ComposioCommand extends Command
{
    public function __construct(
        private ComposioToolChain $composioToolChain,
        private string $anthropicApiKey,
    )
    {
        parent::__construct();
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $io = new SymfonyStyle($input, $output);

        $flow = (new FlowFactory())->create(function() use ($io) {
            yield function() {
                $platform = PlatformFactory::create($this->anthropicApiKey);
                $model = new Claude(Claude::SONNET_37);
        
                $metadataFactory = new MemoryToolFactory();
                foreach($this->composioToolChain->getActions() as $action) {
                    $tool = $this->composioToolChain->getTool($action)[0];
                    $metadataFactory->addTool(ComposioToolChain::class, $tool->name, $tool->description, 'execute');
                }
                $toolbox = new Toolbox($metadataFactory, [$this->composioToolChain]);
                $processor = new AgentProcessor($toolbox);
                $agent = new Agent($platform, $model, [$processor], [$processor]);
                return $agent;
            };
            yield function($agent) {
                $messages = new MessageBag(Message::ofUser('Fetch my last Gmail email'));
                $response = $agent->call($messages);

                return $response;
            };
            yield function($response) use ($io) {
                $io->success($response->getContent());
            };
        });
        
        $ip = new Ip();
        $flow($ip);
        $flow->await();

        return Command::SUCCESS;
    }
}
