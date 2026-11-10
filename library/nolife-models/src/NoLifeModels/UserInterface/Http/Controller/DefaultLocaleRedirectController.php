<?php

declare(strict_types=1);

namespace App\NoLifeModels\UserInterface\Http\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

/**
 * Short URLs without /{_locale}/ prefix → default English locale.
 */
final class DefaultLocaleRedirectController extends AbstractController
{
    private const DEFAULT_LOCALE = 'en';

    #[Route('/embed/compare', name: 'app_nolife_redirect_embed_compare', methods: ['GET', 'POST'])]
    public function embedCompare(Request $request): Response
    {
        return $this->redirectNoLife('no_life_models_embed_compare', $request);
    }

    #[Route('/embed/catalog', name: 'app_nolife_redirect_embed_catalog', methods: ['GET'])]
    public function embedCatalog(Request $request): Response
    {
        return $this->redirectNoLife('no_life_models_embed_catalog', $request);
    }

    #[Route('/embed/ollama', name: 'app_nolife_redirect_embed_ollama', methods: ['GET'])]
    public function embedOllama(Request $request): Response
    {
        return $this->redirectNoLife('no_life_models_embed_ollama', $request);
    }

    #[Route('/embed/benchmark', name: 'app_nolife_redirect_embed_benchmark', methods: ['GET', 'POST'])]
    public function embedBenchmark(Request $request): Response
    {
        return $this->redirectNoLife('no_life_models_embed_benchmark', $request);
    }

    #[Route('/embed/exports', name: 'app_nolife_redirect_embed_exports', methods: ['GET'])]
    public function embedExports(Request $request): Response
    {
        return $this->redirectNoLife('no_life_models_embed_exports', $request);
    }

    #[Route('/dashboard', name: 'app_nolife_redirect_dashboard', methods: ['GET'])]
    public function dashboard(Request $request): Response
    {
        return $this->redirectNoLife('no_life_models_dashboard', $request);
    }

    #[Route('/catalog', name: 'app_nolife_redirect_catalog', methods: ['GET'])]
    public function catalog(Request $request): Response
    {
        return $this->redirectNoLife('no_life_models_catalog', $request);
    }

    #[Route('/ollama', name: 'app_nolife_redirect_ollama', methods: ['GET'])]
    public function ollama(Request $request): Response
    {
        return $this->redirectNoLife('no_life_models_ollama', $request);
    }

    #[Route('/compare', name: 'app_nolife_redirect_compare', methods: ['GET', 'POST'])]
    public function compare(Request $request): Response
    {
        return $this->redirectNoLife('no_life_models_compare', $request);
    }

    #[Route('/benchmark', name: 'app_nolife_redirect_benchmark', methods: ['GET', 'POST'])]
    public function benchmark(Request $request): Response
    {
        return $this->redirectNoLife('no_life_models_benchmark', $request);
    }

    #[Route('/benchmark/result/{id}', name: 'app_nolife_redirect_benchmark_result', methods: ['GET'], requirements: ['id' => '.+'])]
    public function benchmarkResult(Request $request, string $id): Response
    {
        return $this->redirectNoLife('no_life_models_benchmark_result', $request, ['id' => $id]);
    }

    #[Route('/results/{id}', name: 'app_nolife_redirect_results', methods: ['GET'], requirements: ['id' => '.+'])]
    public function results(Request $request, string $id): Response
    {
        return $this->redirectNoLife('no_life_models_results', $request, ['id' => $id]);
    }

    #[Route('/exports', name: 'app_nolife_redirect_exports', methods: ['GET'])]
    public function exports(Request $request): Response
    {
        return $this->redirectNoLife('no_life_models_exports', $request);
    }

    #[Route('/exports/comparison/{id}', name: 'app_nolife_redirect_export_comparison', methods: ['GET'], requirements: ['id' => '.+'])]
    public function exportComparison(Request $request, string $id): Response
    {
        return $this->redirectNoLife('no_life_models_export_comparison', $request, ['id' => $id]);
    }

    #[Route('/exports/benchmark/{id}', name: 'app_nolife_redirect_export_benchmark', methods: ['GET'], requirements: ['id' => '.+'])]
    public function exportBenchmark(Request $request, string $id): Response
    {
        return $this->redirectNoLife('no_life_models_export_benchmark', $request, ['id' => $id]);
    }

    /**
     * @param array<string, string> $routeParams
     */
    private function redirectNoLife(string $routeName, Request $request, array $routeParams = []): Response
    {
        $params = array_merge(
            ['_locale' => self::DEFAULT_LOCALE],
            $routeParams,
            $request->query->all(),
        );

        $status = $request->isMethod('GET') || $request->isMethod('HEAD')
            ? Response::HTTP_FOUND
            : Response::HTTP_TEMPORARY_REDIRECT;

        return $this->redirectToRoute($routeName, $params, $status);
    }
}
