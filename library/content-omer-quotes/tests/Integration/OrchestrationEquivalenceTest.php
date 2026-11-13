<?php
declare(strict_types=1);
namespace App\Tests\Integration;
use App\Application\Port\RepositoryCatalogPort;
use App\Application\Port\RepositoryContentReaderPort;
use App\Application\Port\StoragePort;
use App\Application\Service\ClassificationService;
use App\Application\Service\ClusteringService;
use App\Application\Service\MixedClassifier;
use App\Application\Service\PipelineService;
use App\Application\Service\RepositoryDocumentBuilder;
use App\Application\Service\TextPipeline;
use App\Application\Service\TfIdfService;
use App\Application\Service\SimilarityService;
use App\Application\Service\WordClassTransformer;
use App\Domain\Pipeline\ProcessingReport;
use App\Domain\Repository\RepositoryId;
use App\Domain\Repository\RepositoryMetadata;
use Navi\Application\Workflow\WorkflowRunner;
use Navi\Domain\Execution\Context;
use App\Infrastructure\Orchestration\Flow\PipelineStageAction;
use PHPUnit\Framework\TestCase;

final class OrchestrationEquivalenceTest extends TestCase
{
    public function testMessengerAndFlowEquivalentOnFixture(): void
    {
        $catalog = new class implements RepositoryCatalogPort { public function all(): array { return [new RepositoryMetadata(RepositoryId::fromFullName('a/one'), 'one', 'a/one', 'a', 'php tools', 'none', 'main'), new RepositoryMetadata(RepositoryId::fromFullName('b/two'), 'two', 'b/two', 'b', 'game tools', 'none', 'main')]; } };
        $reader = new class implements RepositoryContentReaderPort { public function readTextMaterial(RepositoryMetadata $metadata): array { return [$metadata->description]; } };
        $s1 = new InMemoryStorage(); $s2 = new InMemoryStorage();
        $p1 = $this->pipeline($catalog, $reader, $s1); $p2 = $this->pipeline($catalog, $reader, $s2);
        $r1 = new ProcessingReport('msg', 'messenger');
        $p1->ingest($r1); $p1->preprocess($r1); $p1->buildFeatures($r1); $p1->classify($r1); $p1->cluster($r1);
        $r2 = new ProcessingReport('flow', 'flow');
        (new WorkflowRunner())->run(Context::fromArray([]), [new PipelineStageAction('ingest', fn(Context $c) => ($p2->ingest($r2) || true) ? $c : $c), new PipelineStageAction('pre', fn(Context $c) => ($p2->preprocess($r2) || true) ? $c : $c), new PipelineStageAction('feat', fn(Context $c) => ($p2->buildFeatures($r2) || true) ? $c : $c), new PipelineStageAction('cls', fn(Context $c) => ($p2->classify($r2) || true) ? $c : $c), new PipelineStageAction('clu', fn(Context $c) => ($p2->cluster($r2) || true) ? $c : $c)]);
        self::assertCount(count($s1->readAll('classification')), $s2->readAll('classification'));
        self::assertCount(count($s1->readAll('clustering')), $s2->readAll('clustering'));
    }

    private function pipeline(RepositoryCatalogPort $catalog, RepositoryContentReaderPort $reader, StoragePort $storage): PipelineService
    {
        return new PipelineService($catalog, $reader, $storage, new RepositoryDocumentBuilder(), new TextPipeline(), new TfIdfService(), new SimilarityService(), new ClassificationService(), new WordClassTransformer(), new MixedClassifier(), new ClusteringService());
    }
}

final class InMemoryStorage implements StoragePort
{
    private array $all = [];
    public function write(string $collection, string $id, array $payload): void { $this->all[$collection][$id] = $payload; }
    public function read(string $collection, string $id): ?array { return $this->all[$collection][$id] ?? null; }
    public function readAll(string $collection): array { return $this->all[$collection] ?? []; }
}
