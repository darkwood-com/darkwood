<?php

namespace App\Controller;

use bitget\internal\BitgetRestClient;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

class TradeController extends AbstractController
{
    #[Route('/api/trade', name: 'app_trade')]
    public function index(): Response
    {
        $restClient = new BitgetRestClient();

        $mixAccountApi = $restClient->getMixClient()->getAccountApi();

        return $this->json([
            'hello' => $mixAccountApi->account("BTCUSDT_UMCBL","USDT"),
        ]);
    }
}
