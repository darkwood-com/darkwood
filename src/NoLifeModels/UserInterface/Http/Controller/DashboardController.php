<?php

declare(strict_types=1);

namespace App\NoLifeModels\UserInterface\Http\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

#[Route('/{_locale}', name: 'no_life_models_', requirements: ['_locale' => 'en|fr'], defaults: ['_locale' => 'en'])]
final class DashboardController extends AbstractController
{
    #[Route('/dashboard', name: 'dashboard', methods: ['GET'])]
    public function __invoke(): Response
    {
        return $this->render('no_life_models/dashboard.html.twig');
    }
}
