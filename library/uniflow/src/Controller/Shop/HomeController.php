<?php

declare(strict_types=1);

namespace App\Controller\Shop;

use App\Model\Page;
use App\Service\ProgramService;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

#[Route('/', name: 'app_shop_')]
class HomeController extends AbstractController
{
    #[Route('', name: 'home', methods: ['GET'])]
    public function home(ProgramService $programService): Response
    {
        return $this->render('shop/home.html.twig', [
            'programs' => array_map($programService->getJsonProgram(...), $programService->findLastPublic(6)),
            'page' => new Page(
                page: 'home',
                title: 'Uniflow',
                description: 'Unified Workflow Automation Tool',
            ),
        ]);
    }
}
