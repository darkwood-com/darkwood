<?php

declare(strict_types=1);

namespace App\NoLifeModels\UserInterface\Http\Controller;

use App\NoLifeModels\Application\Benchmark\RunBenchmarkSuiteHandler;
use App\NoLifeModels\Application\Catalog\SearchCatalogModelsHandler;
use App\NoLifeModels\Application\Comparison\RunPromptComparisonHandler;
use App\NoLifeModels\Application\Comparison\RunPromptComparisonInput;
use App\NoLifeModels\Application\Runtime\ListLocalModelsHandler;
use App\NoLifeModels\Domain\Benchmark\BenchmarkRepositoryInterface;
use App\NoLifeModels\Domain\Catalog\CatalogFilterCriteria;
use App\NoLifeModels\Infrastructure\Session\ComparisonSessionStore;
use App\NoLifeModels\UserInterface\Http\Form\BenchmarkRunFormType;
use App\NoLifeModels\UserInterface\Http\Form\PromptCompareFormType;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

/**
 * Minimal HTML for iframe embedding: no Stimulus / Turbo / LiveComponent JS.
 */
#[Route('/{_locale}', requirements: ['_locale' => 'en|fr'], defaults: ['_locale' => 'en'])]
final class NoLifeEmbedUiController extends AbstractController
{
    private const EMBED_CATALOG_ROW_LIMIT = 80;

    public function __construct(
        private readonly RunPromptComparisonHandler $runComparison,
        private readonly ComparisonSessionStore $sessionStore,
        private readonly ListLocalModelsHandler $listLocalModels,
        private readonly BenchmarkRepositoryInterface $benchmarkRepository,
        private readonly RunBenchmarkSuiteHandler $runBenchmarkSuite,
        private readonly SearchCatalogModelsHandler $searchCatalog,
    ) {
    }

    #[Route('/embed/compare', name: 'no_life_models_embed_compare', methods: ['GET', 'POST'])]
    public function compare(Request $request): Response
    {
        $defaults = [];
        if ($request->isMethod('GET')) {
            $defaults = [
                'modelA' => (string) $request->query->get('modelA', ''),
                'modelB' => (string) $request->query->get('modelB', ''),
            ];
        }
        $form = $this->createForm(PromptCompareFormType::class, $defaults);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            /** @var array{modelA: string, modelB: string, prompt: string, system?: string|null, temperature: float|string, topP: float|string} $data */
            $data = $form->getData();
            $input = new RunPromptComparisonInput(
                trim((string) $data['modelA']),
                trim((string) $data['modelB']),
                trim((string) $data['prompt']),
                isset($data['system']) && null !== $data['system'] ? trim((string) $data['system']) : null,
                (float) $data['temperature'],
                (float) $data['topP'],
            );
            $result = $this->runComparison->handle($input);
            $this->sessionStore->save($result);

            return $this->redirectToRoute('no_life_models_embed_compare_result', [
                '_locale' => $request->attributes->getString('_locale'),
                'id' => $result->id,
            ]);
        }

        return $this->render('embed/compare.html.twig', [
            'form' => $form,
        ]);
    }

    #[Route('/embed/compare/result/{id}', name: 'no_life_models_embed_compare_result', methods: ['GET'], requirements: ['id' => '.+'])]
    public function compareResult(string $id, Request $request): Response
    {
        $comparison = $this->sessionStore->load($id);
        if (null === $comparison) {
            throw $this->createNotFoundException();
        }

        return $this->render('embed/results_compare.html.twig', [
            'comparison' => $comparison,
        ]);
    }

    #[Route('/embed/ollama', name: 'no_life_models_embed_ollama', methods: ['GET'])]
    public function ollama(): Response
    {
        $error = null;
        $models = [];
        try {
            $models = $this->listLocalModels->handle();
        } catch (\Throwable $e) {
            $error = $e->getMessage();
        }

        return $this->render('embed/ollama.html.twig', [
            'models' => $models,
            'ollamaError' => $error,
        ]);
    }

    #[Route('/embed/benchmark', name: 'no_life_models_embed_benchmark', methods: ['GET', 'POST'])]
    public function benchmark(Request $request): Response
    {
        $form = $this->createForm(BenchmarkRunFormType::class);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            /** @var array{modelA: string, modelB: string} $data */
            $data = $form->getData();
            $report = $this->runBenchmarkSuite->handle([trim($data['modelA']), trim($data['modelB'])]);
            $this->sessionStore->saveBenchmarkReport($report);

            return $this->redirectToRoute('no_life_models_embed_benchmark_result', [
                '_locale' => $request->attributes->getString('_locale'),
                'id' => $report->id,
            ]);
        }

        return $this->render('embed/benchmark.html.twig', [
            'form' => $form,
            'cases' => $this->benchmarkRepository->findAll(),
        ]);
    }

    #[Route('/embed/benchmark/result/{id}', name: 'no_life_models_embed_benchmark_result', methods: ['GET'], requirements: ['id' => '.+'])]
    public function benchmarkResult(string $id): Response
    {
        $report = $this->sessionStore->loadBenchmarkReport($id);
        if (null === $report) {
            throw $this->createNotFoundException();
        }

        return $this->render('embed/benchmark_result.html.twig', [
            'report' => $report,
        ]);
    }

    #[Route('/embed/exports', name: 'no_life_models_embed_exports', methods: ['GET'])]
    public function exports(): Response
    {
        return $this->render('embed/exports.html.twig', [
            'comparisonIds' => $this->sessionStore->listRecentIds(),
            'benchmarkIds' => $this->sessionStore->listBenchmarkIds(),
        ]);
    }

    #[Route('/embed/catalog', name: 'no_life_models_embed_catalog', methods: ['GET'])]
    public function catalog(): Response
    {
        $rows = $this->searchCatalog->handle(CatalogFilterCriteria::empty());
        $truncated = \count($rows) > self::EMBED_CATALOG_ROW_LIMIT;
        $rows = \array_slice($rows, 0, self::EMBED_CATALOG_ROW_LIMIT);

        return $this->render('embed/catalog.html.twig', [
            'rows' => $rows,
            'truncated' => $truncated,
            'rowLimit' => self::EMBED_CATALOG_ROW_LIMIT,
        ]);
    }
}
