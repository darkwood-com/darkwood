<?php

namespace App\Command;

use App\Agent\Runtime\AgentRunId;
use App\Agent\Builtin\ContentAgent;
use App\Agent\Runtime\AgentContext;
use App\Agent\Runtime\AgentInput;
use App\Demo\ClientWebsiteDemoInputFactory;
use App\Demo\ClientWebsiteRunWriter;
use App\Demo\DeliveryReportWriter;
use App\Demo\RunDirectory;
use App\Integration\Ai\AiAgentClient;
use App\Integration\Ai\AiAgentRequest;
use App\Integration\Ai\ConfiguredAiAgentClient;
use App\Integration\Ai\SymfonyAiAgentClient;
use App\Integration\Flow\FlowWorkflowClient;
use App\Integration\Navi\NaviTraceClient;
use App\Service\AgentRunner;
use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;

#[AsCommand(name: 'nolife:demo:client-website', description: 'Run the Acme SaaS client website demo.')]
final class DemoClientWebsiteCommand extends Command
{
    public function __construct(
        private readonly AgentRunner $runner,
        private readonly ClientWebsiteDemoInputFactory $inputFactory,
        private readonly ClientWebsiteRunWriter $runWriter,
        private readonly DeliveryReportWriter $deliveryReportWriter,
        private readonly AiAgentClient $aiClient,
        private readonly FlowWorkflowClient $flowClient,
        private readonly NaviTraceClient $naviClient,
        private readonly ?string $configuredAiAdapter,
        private readonly string $runRootDir,
    ) {
        parent::__construct();
    }

    protected function configure(): void
    {
        $this
            ->addArgument('client', InputArgument::REQUIRED)
            ->addOption('real-ai', null, InputOption::VALUE_NONE)
            ->addOption('real-flow', null, InputOption::VALUE_NONE)
            ->addOption('real-navi', null, InputOption::VALUE_NONE)
            ->addOption('real-media', null, InputOption::VALUE_NONE)
            ->addOption('dry-run', null, InputOption::VALUE_NONE);
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $client = (string) $input->getArgument('client');
        $runId = AgentRunId::generate()->value;
        $runPath = rtrim($this->runRootDir, '/').'/'.$runId;
        if (!is_dir($runPath)) {
            mkdir($runPath, 0775, true);
        }

        $brief = $this->inputFactory->create($client);
        $adapterIntent = [
            'ai' => (bool) $input->getOption('real-ai'),
            'flow' => (bool) $input->getOption('real-flow'),
            'navi' => (bool) $input->getOption('real-navi'),
            'media' => (bool) $input->getOption('real-media'),
            'dry_run' => (bool) $input->getOption('dry-run'),
        ];
        $output->writeln(sprintf('AI adapter: %s', $this->aiClient->name()));
        $output->writeln(sprintf('Using %s AI adapter', $this->aiClient->name() === 'symfony_ai' ? 'Symfony' : 'Null'));
        $output->writeln(sprintf('Using Flow adapter: %s', $this->flowClient->name()));
        $output->writeln(sprintf('Using Navi adapter: %s', $this->naviClient->name()));

        $realAi = $adapterIntent['ai'] === true;
        $realFlow = $adapterIntent['flow'] === true;
        $realNavi = $adapterIntent['navi'] === true;
        if ($realAi) {
            $output->writeln('Real AI mode enabled');
            if (($this->configuredAiAdapter ?? 'null') !== 'symfony_ai') {
                throw new \RuntimeException('--real-ai requires NOLIFE_AI_ADAPTER=symfony_ai');
            }
            $resolvedClass = $this->aiClient instanceof ConfiguredAiAgentClient
                ? $this->aiClient->resolvedClientClass()
                : $this->aiClient::class;
            if ($resolvedClass !== SymfonyAiAgentClient::class) {
                throw new \RuntimeException(sprintf('--real-ai requires SymfonyAiAgentClient, got %s', $resolvedClass));
            }
        }
        if ($realFlow && $this->flowClient->name() !== 'darkwood_flow') {
            throw new \RuntimeException(sprintf('--real-flow requires DarkwoodFlowWorkflowClient, got %s', $this->flowClient->name()));
        }
        if ($realNavi && $this->naviClient->name() !== 'darkwood_navi') {
            throw new \RuntimeException(sprintf('--real-navi requires DarkwoodNaviTraceClient, got %s', $this->naviClient->name()));
        }

        $results = $this->runner->runWorkflow($brief, [
            'run_id' => $runId,
            'requester' => 'cli',
            'project_key' => $client,
            'metadata' => [
                'workflow_state' => 'lead_qualified',
                'approved_by_human' => true,
                'known_workflow_states' => [
                    'lead_qualified',
                    'brief_created',
                    'architecture_defined',
                    'content_generated',
                    'seo_optimized',
                    'media_brief_created',
                    'delivery_reviewed',
                    'human_approval_required',
                ],
                'adapter_intent' => $adapterIntent,
            ],
        ]);

        $workflowSummary = [
            'status' => 'completed',
            'final_state' => 'human_approval_required',
            'steps' => array_keys($results),
            'adapter_intent' => $adapterIntent,
            'adapter_usage' => [
                'ai' => $this->aiClient->name(),
                'flow' => $this->flowClient->name(),
                'navi' => $this->naviClient->name(),
            ],
        ];

        $runDirectory = new RunDirectory($runId, $runPath);
        $this->runWriter->write($runDirectory, $brief, $workflowSummary);
        if ($realAi) {
            $article = $this->generateRealAiArticle($runId, $client, $output);
            if (str_contains($article, 'Long-form article draft placeholder.')) {
                throw new \RuntimeException('Real AI mode produced placeholder content.');
            }
            $articlePath = $runPath.'/article.md';
            $output->writeln(sprintf('Writing AI output to: %s', $articlePath));
            file_put_contents($articlePath, $article);
        }
        $this->deliveryReportWriter->write($runDirectory, $workflowSummary);
        $candidateAuditSources = [
            rtrim($this->runRootDir, '/').'/audit/agent-runs/'.$runId.'.jsonl',
            dirname(dirname(rtrim($this->runRootDir, '/'))).'/audit/agent-runs/'.$runId.'.jsonl',
        ];
        $auditSource = null;
        foreach ($candidateAuditSources as $candidate) {
            if (is_file($candidate)) {
                $auditSource = $candidate;
                break;
            }
        }
        if ($auditSource === null) {
            throw new \RuntimeException(sprintf('Audit source file not found for run "%s".', $runId));
        }
        copy($auditSource, $runPath.'/audit.jsonl');

        if ($realNavi) {
            $tracePath = $runPath.'/trace.json';
            if (!is_file($tracePath)) {
                throw new \RuntimeException('Real Navi mode requires trace.json to be written.');
            }
            $trace = json_decode((string) file_get_contents($tracePath), true);
            if (!is_array($trace) || !isset($trace['events']) || !is_array($trace['events']) || $trace['events'] === []) {
                throw new \RuntimeException('Real Navi mode requires at least one trace event.');
            }
        }

        $output->writeln(sprintf('run_id: %s', $runId));
        $traceId = $this->resolveTraceId($runPath, $runId, $realNavi);
        $output->writeln(sprintf('trace_id: %s', $traceId));
        $output->writeln('final_state: human_approval_required');
        $output->writeln('workflow_status: completed');
        $output->writeln(sprintf('output_dir: %s', $runPath));

        return Command::SUCCESS;
    }

    private function resolveTraceId(string $runPath, string $runId, bool $realNavi): string
    {
        if (!$realNavi) {
            return sprintf('local-trace-%s', $runId);
        }

        $tracePath = $runPath.'/trace.json';
        $trace = is_file($tracePath) ? json_decode((string) file_get_contents($tracePath), true) : null;
        if (!is_array($trace) || !is_string($trace['trace_id'] ?? null)) {
            throw new \RuntimeException('Unable to resolve real Navi trace id from trace.json.');
        }

        return $trace['trace_id'];
    }

    private function generateRealAiArticle(string $runId, string $client, OutputInterface $output): string
    {
        $prompt = 'Generate a landing page for Acme SaaS. Include headline, subheadline, sections, CTA, and article-style supporting copy.';
        $output->writeln('Using Symfony AI adapter');
        $output->writeln('Calling Symfony AI agent');
        $output->writeln('AI prompt: Generate a landing page for Acme SaaS');
        $response = $this->aiClient->run(new AiAgentRequest(
            agent: new ContentAgent(),
            input: new AgentInput(
                payload: ['client' => $client, 'task' => 'landing_page_article'],
                metadata: [
                    'real_ai' => true,
                    'allow_ai_fallback' => false,
                    'ai_options' => ['temperature' => 0.9],
                ],
            ),
            context: new AgentContext($runId, 'cli', $client, null, []),
            systemPrompt: 'You are a senior conversion copywriter for SaaS landing pages.',
            userPrompt: $prompt,
            options: ['temperature' => 0.9],
            responseFormat: null,
            allowFallback: false,
        ));
        $output->writeln('Symfony AI call completed');

        $content = $response->payload['content'] ?? null;
        if (is_string($content)) {
            if (trim($content) === '') {
                throw new \RuntimeException('Real AI mode returned empty article content.');
            }

            return $content;
        }
        if (is_array($content) && $content !== []) {
            return json_encode($content, \JSON_PRETTY_PRINT | \JSON_THROW_ON_ERROR);
        }

        throw new \RuntimeException('Real AI mode returned empty article content.');
    }
}
