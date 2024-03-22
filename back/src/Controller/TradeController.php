<?php

namespace App\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

class TradeController extends AbstractController
{
    #[Route('/api/trade', name: 'app_trade')]
    public function index(): Response
    {
        return $this->json([
            'hello' => true,
        ]);
    }
}
