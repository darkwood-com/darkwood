<?php

namespace App\Controller;

use App\Service\Bitget;
use bitget\internal\BitgetRestClient;
use bitget\internal\BitgetWsHandle;
use bitget\internal\Listener;
use bitget\model\ws\SubscribeReq;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use WebSocket\Client;

class TradeController extends AbstractController
{
    private Bitget $bitget;

    public function __construct(Bitget $bitget)
    {
        $this->bitget = $bitget;
    }

    #[Route('/api/ticker', name: 'app_ticker')]
    public function ticker(): Response
    {
        $data = json_decode($this->bitget->getTicker([
            'symbol' => 'BTCUSDT',
            'productType' => 'USDT-FUTURES',
        ]));

        return $this->json($data);
    }
}
