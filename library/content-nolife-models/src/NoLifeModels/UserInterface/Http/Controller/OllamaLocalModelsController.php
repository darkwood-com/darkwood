<?php

declare(strict_types=1);

namespace App\NoLifeModels\UserInterface\Http\Controller;

use App\NoLifeModels\Application\Runtime\ListLocalModelsHandler;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

#[Route('/{_locale}', name: 'no_life_models_', requirements: ['_locale' => 'en|fr'], defaults: ['_locale' => 'en'])]
final class OllamaLocalModelsController extends AbstractController
{
    public function __construct(
        private readonly ListLocalModelsHandler $listLocalModels,
    ) {
    }

    #[Route('/ollama', name: 'ollama', methods: ['GET'])]
    public function __invoke(): Response
    {
        $error = null;
        $models = [];
        try {
            $models = $this->listLocalModels->handle();
        } catch (\Throwable $e) {
            $error = $e->getMessage();
        }

        return $this->render('no_life_models/ollama.html.twig', [
            'models' => $models,
            'ollamaError' => $error,
        ]);
    }
}
