<?php

declare(strict_types=1);

namespace App\Application\Service;

use App\Application\Port\RepositoryCatalogPort;
use App\Application\Port\RepositoryContentReaderPort;
use App\Application\Port\StoragePort;
use App\Domain\Pipeline\ProcessingReport;

final readonly class PipelineService
{
    public function __construct(
        private RepositoryCatalogPort $catalog,
        private RepositoryContentReaderPort $contentReader,
        private StoragePort $storage,
        private RepositoryDocumentBuilder $documentBuilder,
        private TextPipeline $textPipeline,
        private TfIdfService $tfIdf,
        private SimilarityService $similarity,
        private ClassificationService $classification,
        private WordClassTransformer $wordClassTransformer,
        private MixedClassifier $mixedClassifier,
        private ClusteringService $clustering,
    ) {
    }

    public function ingest(ProcessingReport $report): void
    {
        foreach ($this->catalog->all() as $metadata) {
            try {
                $fragments = $this->contentReader->readTextMaterial($metadata);
                $doc = $this->documentBuilder->build($metadata, $fragments);
                $this->storage->write('documents', $metadata->id->toString(), [
                    'id' => $metadata->id->toString(),
                    'fullName' => $metadata->fullName,
                    'canonicalText' => $doc->toCanonicalText(),
                ]);
                $this->storage->write('ingestion', $metadata->id->toString(), ['status' => 'ok']);
            } catch (\Throwable $e) {
                $this->storage->write('ingestion', $metadata->id->toString(), ['status' => 'error', 'error' => $e->getMessage()]);
                $report->addError('ingest', $e->getMessage(), ['repo' => $metadata->fullName]);
            }
        }
        $report->addStep('ingest', ['count' => count($this->storage->readAll('documents'))]);
    }

    public function preprocess(ProcessingReport $report): void
    {
        foreach ($this->storage->readAll('documents') as $id => $doc) {
            $normalized = $this->textPipeline->normalize((string) ($doc['canonicalText'] ?? ''));
            $tokens = $this->textPipeline->tokenize($normalized);
            $this->storage->write('corpus', $id, ['normalized' => $normalized, 'tokens' => $tokens, 'fullName' => $doc['fullName'] ?? '']);
        }
        $report->addStep('preprocess', ['count' => count($this->storage->readAll('corpus'))]);
    }

    public function buildFeatures(ProcessingReport $report): void
    {
        $tokenByDoc = [];
        foreach ($this->storage->readAll('corpus') as $id => $item) {
            $tokenByDoc[$id] = (array) ($item['tokens'] ?? []);
        }
        $computed = $this->tfIdf->compute($tokenByDoc);
        $this->storage->write('vectors', '_meta', ['df' => $computed['df'], 'idf' => $computed['idf']]);
        foreach ($computed['vectors'] as $id => $vector) {
            $this->storage->write('vectors', $id, ['weights' => $vector]);
        }
        $report->addStep('features', ['count' => count($computed['vectors'])]);
    }

    public function classify(ProcessingReport $report): void
    {
        $vectors = $this->vectorMap();
        $labels = [];
        foreach ($this->storage->readAll('corpus') as $id => $item) {
            $labels[$id] = $this->classification->heuristicLabel($id, (string) ($item['fullName'] ?? ''));
        }
        $base = $this->classification->classify($vectors, $labels);
        $wcVectors = [];
        foreach ($vectors as $id => $vector) {
            $wcVectors[$id] = $this->wordClassTransformer->transform($vector);
        }
        $wc = $this->classification->classify($wcVectors, $labels);
        $mixed = $this->mixedClassifier->combine($base, $wc);
        foreach ($mixed as $id => $result) {
            $this->storage->write('classification', $id, $result);
        }
        $report->addStep('classification', ['count' => count($mixed)]);
    }

    public function cluster(ProcessingReport $report): void
    {
        $assignments = $this->clustering->assign($this->vectorMap(), 3);
        foreach ($assignments as $id => $cluster) {
            $this->storage->write('clustering', $id, ['cluster' => $cluster]);
        }
        $report->addStep('clustering', ['count' => count($assignments)]);
    }

    /**
     * @return list<array{id:string,score:float}>
     */
    public function topSimilarity(string $repoId, int $top): array
    {
        $vectors = $this->vectorMap();
        $source = $vectors[$repoId] ?? [];
        $scores = [];
        foreach ($vectors as $id => $vector) {
            if ($id === $repoId) {
                continue;
            }
            $scores[] = ['id' => $id, 'score' => $this->similarity->cosine($source, $vector)];
        }
        usort($scores, static fn (array $a, array $b): int => $b['score'] <=> $a['score']);

        return array_slice($scores, 0, $top);
    }

    public function persistReport(ProcessingReport $report): void
    {
        $this->storage->write('reports', $report->runId, [
            'runId' => $report->runId,
            'orchestration' => $report->orchestration,
            'steps' => $report->steps,
            'errors' => $report->errors,
        ]);
    }

    /**
     * @return array<string, array<string, float>>
     */
    private function vectorMap(): array
    {
        $all = $this->storage->readAll('vectors');
        unset($all['_meta']);
        $out = [];
        foreach ($all as $id => $item) {
            $out[$id] = (array) ($item['weights'] ?? []);
        }

        return $out;
    }
}
