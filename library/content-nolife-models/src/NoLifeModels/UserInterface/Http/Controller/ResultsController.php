<?php

declare(strict_types=1);

namespace App\NoLifeModels\UserInterface\Http\Controller;

use App\NoLifeModels\Infrastructure\Session\ComparisonSessionStore;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

#[Route('/{_locale}', name: 'no_life_models_', requirements: ['_locale' => 'en|fr'], defaults: ['_locale' => 'en'])]
final class ResultsController extends AbstractController
{
    public function __construct(
        private readonly ComparisonSessionStore $sessionStore,
    ) {
    }

    #[Route('/results/{id}', name: 'results', methods: ['GET'])]
    public function __invoke(string $id): Response
    {
        $comparison = $this->sessionStore->load($id);
        if (null === $comparison) {
            throw $this->createNotFoundException();
        }

        return $this->render('no_life_models/results.html.twig', [
            'comparison' => $comparison,
        ]);
    }
}
