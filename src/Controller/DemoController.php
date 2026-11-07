<?php

declare(strict_types=1);

namespace App\Controller;

use App\Service\MemoryExamples;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\Form\Extension\Core\Type\CheckboxType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\Extension\Core\Type\IntegerType;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Validator\Constraints as Assert;

final class DemoController extends AbstractController
{
    #[Route('/', name: 'home', methods: ['GET'])]
    public function home(): Response
    {
        return $this->render('home.html.twig', ['demos' => MemoryExamples::DEMOS]);
    }

    #[Route('/demo/{slug}', name: 'demo', requirements: ['slug' => 'darkwood|flow|uniflow|darkwaar|djmatyo|heroclip'], methods: ['GET', 'POST'])]
    public function demo(string $slug, Request $request, MemoryExamples $examples): Response
    {
        $builder = $this->createFormBuilder(null, ['csrf_token_id' => $slug]);
        if ('flow' === $slug) {
            $builder->add('n', IntegerType::class, ['label' => 'Additionner de 1 à', 'data' => 1000, 'constraints' => [new Assert\NotBlank(), new Assert\Range(min: 1, max: 100000)]]);
        }
        if ('heroclip' === $slug) {
            $builder->add('character', TextType::class, ['label' => 'Personnage du premier plan', 'data' => 'Nora', 'constraints' => [new Assert\NotBlank(), new Assert\Length(max: 24)]])
                ->add('palette', ChoiceType::class, ['label' => 'Couleur du premier plan', 'choices' => ['Ambre' => 'ambre', 'Bleu' => 'bleu', 'Vert' => 'vert']])
                ->add('memory', CheckboxType::class, ['label' => 'Réutiliser les repères du plan précédent', 'required' => false, 'data' => true]);
        }
        $buttons = match ($slug) {
            'flow' => ['save' => 'Conserver le résultat', 'clear' => 'Effacer le résultat'],
            'uniflow' => ['start' => 'Lancer et mettre en pause', 'resume' => 'Reprendre la dernière exécution', 'clear' => 'Effacer les exécutions'],
            'darkwaar' => ['observe' => 'Lire l’inscription', 'rewind' => 'Revenir au début', 'moon' => 'Lune', 'star' => 'Étoile', 'sun' => 'Soleil', 'forget' => 'Tout oublier'],
            'heroclip' => ['next' => 'Créer le plan suivant', 'clear' => 'Recommencer le storyboard'],
            default => [],
        };
        foreach ($buttons as $name => $label) {
            $builder->add($name, SubmitType::class, ['label' => $label]);
        }
        $form = $builder->getForm();
        $form->handleRequest($request);
        if ($form->isSubmitted() && $form->isValid() && $buttons) {
            $examples->act($slug, $form->getClickedButton()?->getName() ?? '', $form->getData(), $request->getSession());
            return $this->redirectToRoute('demo', ['slug' => $slug], Response::HTTP_SEE_OTHER);
        }
        $id = strtoupper(trim($request->query->getString('id', 'MEM-001')));
        return $this->render('demo.html.twig', [
            'slug' => $slug, 'demos' => MemoryExamples::DEMOS, 'demo' => MemoryExamples::DEMOS[$slug],
            'form' => $form, 'decisionId' => $id, 'decision' => 'darkwood' === $slug ? $examples->decision($id) : null,
            'calculation' => 'flow' === $slug ? $examples->calculation(max(1, min(100000, (int) ($form->get('n')->getData() ?? 1000)))) : null,
            'stored' => $request->getSession()->get('flow_result'),
            'workflow' => 'uniflow' === $slug ? $examples->workflow() : null,
            'runs' => $request->getSession()->get('runs', []),
            'game' => $request->getSession()->get('game', ['knowledge' => false, 'phase' => 'room', 'loops' => 0]),
            'scenes' => $request->getSession()->get('scenes', []),
        ], new Response(status: $form->isSubmitted() ? 422 : 200));
    }
}
