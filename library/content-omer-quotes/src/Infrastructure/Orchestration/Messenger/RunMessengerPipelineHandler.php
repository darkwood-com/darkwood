<?php
declare(strict_types=1);
namespace App\Infrastructure\Orchestration\Messenger;
use App\Application\Service\PipelineService;
use App\Domain\Pipeline\ProcessingReport;
use App\Infrastructure\Orchestration\Messenger\Message\RunMessengerPipeline;
use Symfony\Component\Messenger\Attribute\AsMessageHandler;

#[AsMessageHandler]
final readonly class RunMessengerPipelineHandler
{
    public function __construct(private PipelineService $pipeline) {}
    public function __invoke(RunMessengerPipeline $message): void
    {
        $report = new ProcessingReport($message->runId, 'messenger');
        $this->pipeline->ingest($report);
        $this->pipeline->preprocess($report);
        $this->pipeline->buildFeatures($report);
        $this->pipeline->classify($report);
        $this->pipeline->cluster($report);
        $this->pipeline->persistReport($report);
    }
}
