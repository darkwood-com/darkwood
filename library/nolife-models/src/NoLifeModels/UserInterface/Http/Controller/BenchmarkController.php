<?php

declare(strict_types=1);

namespace App\NoLifeModels\UserInterface\Http\Controller;

use App\NoLifeModels\Application\Benchmark\RunBenchmarkSuiteHandler;
use App\NoLifeModels\Domain\Benchmark\BenchmarkRepositoryInterface;
use App\NoLifeModels\Infrastructure\Session\ComparisonSessionStore;
use App\NoLifeModels\UserInterface\Http\Form\BenchmarkRunFormType;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

#[Route('/{_locale}', name: 'no_life_models_', requirements: ['_locale' => 'en|fr'], defaults: ['_locale' => 'en'])]
final class BenchmarkController extends AbstractController
{
    public function __construct(
        private readonly BenchmarkRepositoryInterface $benchmarkRepository,
        private readonly RunBenchmarkSuiteHandler $runBenchmarkSuite,
        private readonly ComparisonSessionStore $sessionStore,
    ) {
    }

    #[Route('/benchmark', name: 'benchmark', methods: ['GET', 'POST'])]
    public function __invoke(Request $request): Response
    {
        $form = $this->createForm(BenchmarkRunFormType::class);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            /** @var array{modelA: string, modelB: string} $data */
            $data = $form->getData();
            $report = $this->runBenchmarkSuite->handle([trim($data['modelA']), trim($data['modelB'])]);
            $this->sessionStore->saveBenchmarkReport($report);

            return $this->redirectToRoute('no_life_models_benchmark_result', [
                '_locale' => $request->attributes->getString('_locale'),
                'id' => $report->id,
            ]);
        }

        return $this->render('no_life_models/benchmark.html.twig', [
            'form' => $form,
            'cases' => $this->benchmarkRepository->findAll(),
        ]);
    }

    #[Route('/benchmark/result/{id}', name: 'benchmark_result', methods: ['GET'])]
    public function result(string $id, Request $request): Response
    {
        $report = $this->sessionStore->loadBenchmarkReport($id);
        if (null === $report) {
            throw $this->createNotFoundException();
        }

        return $this->render('no_life_models/benchmark_result.html.twig', [
            'report' => $report,
        ]);
    }
}
