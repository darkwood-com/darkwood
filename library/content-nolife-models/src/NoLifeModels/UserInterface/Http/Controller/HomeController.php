<?php

declare(strict_types=1);

namespace App\NoLifeModels\UserInterface\Http\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

final class HomeController extends AbstractController
{
    #[Route('/', name: 'app_home', methods: ['GET'])]
    public function __invoke(): Response
    {
        return $this->redirectToRoute('no_life_models_dashboard', ['_locale' => 'en']);
    }
}
