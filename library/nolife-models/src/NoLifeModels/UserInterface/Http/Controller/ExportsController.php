<?php

declare(strict_types=1);

namespace App\NoLifeModels\UserInterface\Http\Controller;

use App\NoLifeModels\Domain\Export\ExportFormat;
use App\NoLifeModels\Domain\Export\ResultExporterInterface;
use App\NoLifeModels\Infrastructure\Session\ComparisonSessionStore;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

#[Route('/{_locale}', name: 'no_life_models_', requirements: ['_locale' => 'en|fr'], defaults: ['_locale' => 'en'])]
final class ExportsController extends AbstractController
{
    public function __construct(
        private readonly ComparisonSessionStore $sessionStore,
        private readonly ResultExporterInterface $exporter,
    ) {
    }

    #[Route('/exports', name: 'exports', methods: ['GET'])]
    public function index(): Response
    {
        return $this->render('no_life_models/exports.html.twig', [
            'comparisonIds' => $this->sessionStore->listRecentIds(),
            'benchmarkIds' => $this->sessionStore->listBenchmarkIds(),
        ]);
    }

    #[Route('/exports/comparison/{id}', name: 'export_comparison', methods: ['GET'])]
    public function exportComparison(string $id, Request $request): Response
    {
        $format = ExportFormat::tryFrom((string) $request->query->get('format', 'json')) ?? ExportFormat::Json;
        $comparison = $this->sessionStore->load($id);
        if (null === $comparison) {
            throw $this->createNotFoundException();
        }
        $body = $this->exporter->exportPromptComparison($comparison, $format);
        $filename = sprintf('nolife-comparison-%s.%s', $id, $format->value);

        return $this->fileDownloadResponse($body, $filename, $format);
    }

    #[Route('/exports/benchmark/{id}', name: 'export_benchmark', methods: ['GET'])]
    public function exportBenchmark(string $id, Request $request): Response
    {
        $format = ExportFormat::tryFrom((string) $request->query->get('format', 'json')) ?? ExportFormat::Json;
        $report = $this->sessionStore->loadBenchmarkReport($id);
        if (null === $report) {
            throw $this->createNotFoundException();
        }
        $body = $this->exporter->exportBenchmarkReport($report, $format);
        $filename = sprintf('nolife-benchmark-%s.%s', $id, $format->value);

        return $this->fileDownloadResponse($body, $filename, $format);
    }

    private function fileDownloadResponse(string $body, string $filename, ExportFormat $format): Response
    {
        $mime = match ($format) {
            ExportFormat::Json => 'application/json',
            ExportFormat::Csv => 'text/csv; charset=UTF-8',
            ExportFormat::Markdown => 'text/markdown; charset=UTF-8',
        };
        $response = new Response($body);
        $response->headers->set('Content-Type', $mime);
        $response->headers->set('Content-Disposition', 'attachment; filename="'.$filename.'"');

        return $response;
    }
}
