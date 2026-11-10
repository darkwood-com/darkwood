<?php

declare(strict_types=1);

namespace App\NoLifeModels\UserInterface\Http\Controller;

use App\NoLifeModels\Application\Comparison\RunPromptComparisonHandler;
use App\NoLifeModels\Application\Comparison\RunPromptComparisonInput;
use App\NoLifeModels\Infrastructure\Session\ComparisonSessionStore;
use App\NoLifeModels\UserInterface\Http\Form\PromptCompareFormType;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

#[Route('/{_locale}', name: 'no_life_models_', requirements: ['_locale' => 'en|fr'], defaults: ['_locale' => 'en'])]
final class CompareController extends AbstractController
{
    public function __construct(
        private readonly RunPromptComparisonHandler $runComparison,
        private readonly ComparisonSessionStore $sessionStore,
    ) {
    }

    #[Route('/compare', name: 'compare', methods: ['GET', 'POST'])]
    public function __invoke(Request $request): Response
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
                isset($data['system']) && $data['system'] !== null ? trim((string) $data['system']) : null,
                (float) $data['temperature'],
                (float) $data['topP'],
            );
            $result = $this->runComparison->handle($input);
            $this->sessionStore->save($result);

            return $this->redirectToRoute('no_life_models_results', [
                '_locale' => (string) $request->attributes->get('_locale'),
                'id' => $result->id,
            ]);
        }

        return $this->render('no_life_models/compare.html.twig', [
            'form' => $form,
        ]);
    }
}
