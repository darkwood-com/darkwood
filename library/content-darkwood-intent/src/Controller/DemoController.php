<?php

declare(strict_types=1);

namespace App\Controller;

use App\Service\IntentExamples;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

final class DemoController extends AbstractController
{
    #[Route('/', name: 'home', methods: ['GET'])]
    public function home(): Response
    {
        return $this->render('home.html.twig', ['demos' => IntentExamples::DEMOS]);
    }

    #[Route('/demo/{slug}', name: 'demo', requirements: ['slug' => 'darkwaar|heroclip|djmatyo|flow|uniflow'], methods: ['GET'])]
    public function demo(string $slug): Response
    {
        return $this->render('demo.html.twig', [
            'slug' => $slug,
            'demos' => IntentExamples::DEMOS,
            'demo' => IntentExamples::DEMOS[$slug],
        ]);
    }
}
