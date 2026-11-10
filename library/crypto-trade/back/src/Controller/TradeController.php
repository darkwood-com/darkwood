<?php

namespace App\Controller;

use App\Service\Bitget;
use bitget\internal\BitgetRestClient;
use bitget\internal\BitgetWsHandle;
use bitget\internal\Listener;
use bitget\model\ws\SubscribeReq;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
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

    #[Route('/api/tickers', name: 'app_tickers')]
    public function tickers(): Response
    {
        $data = json_decode($this->bitget->getTickers([
            'productType' => 'USDT-FUTURES',
        ]));

        return $this->json($data);
    }

    #[Route('/api/ticker', name: 'app_ticker')]
    public function ticker(Request $request): Response
    {
        $data = json_decode($this->bitget->getTicker([
            'symbol' => $request->getPayload()->get('symbol'),
            'productType' => 'USDT-FUTURES',
        ]));

        return $this->json($data);
    }

    #[Route('/api/account', name: 'app_account')]
    public function account(): Response
    {
        $data = json_decode($this->bitget->getAccount([
            'productType' => 'USDT-FUTURES',
        ]));

        return $this->json($data);
    }

    #[Route('/api/positions', name: 'app_positions')]
    public function positions(): Response
    {
        $data = json_decode($this->bitget->getPositions([
            'productType' => 'USDT-FUTURES',
            'marginCoin' => 'usdt',
        ]));

        return $this->json($data);
    }

    #[Route('/api/order', name: 'app_order')]
    public function order(): Response
    {
        // https://www.bitget.com/api-doc/contract/trade/Place-Order
        $data = json_decode($this->bitget->placeOrder([
            "symbol" => "BTCUSDT",
            "productType" => "USDT-FUTURES",
            "marginMode" => "isolated",
            "marginCoin" => "USDT",
            "size" => "0.001",
            "price" => "64442",
            "side" => "buy",
            "tradeSide" => "open",
            "orderType" => "limit",
            //"force" => "gtc",
            //"clientOid" => "121211212122",
        ]));

        return $this->json($data);
    }

    #[Route('/api/set-margin-mode', name: 'app_set_margin_mode')]
    public function setMarginMode(): Response
    {
        $data = json_decode($this->bitget->setLeverage([
            "symbol" => "BTCUSDT",
            "productType" => "USDT-FUTURES",
            "marginCoin" => "USDT",
            "leverage" => "125",
            "holdSide" =>"long",
        ]));

        return $this->json($data);
    }
}
