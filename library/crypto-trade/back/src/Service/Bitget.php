<?php

namespace App\Service;

use App\Service\RsaUtil;

class Bitget
{
    const POST = "POST";
    const GET = "GET";
    const websocketUrl = "wss://ws.bitget.com/mix/v1/stream";
    const restApiUrl = "https://api.bitget.com";
    const signType = "SHA256";

    public function __construct(
        private string $apiSecret,
        private string $apiKey,
        private string $passphrase
    )
    {
    }

    public function getTickers($params)
    {
        return $this->doGet("/api/v2/mix/market/tickers", $params);
    }

    public function getTicker($params)
    {
        return $this->doGet("/api/v2/mix/market/ticker", $params);
    }

    public function getAccount($params)
    {
        return $this->doGet("/api/v2/mix/account/accounts", $params);
    }

    public function getPositions($params)
    {
        return $this->doGet("/api/v2/mix/position/all-position", $params);
    }

    public function placeOrder($params)
    {
        return $this->doPost("/api/v2/mix/order/place-order", $params);
    }

    public function setLeverage($params)
    {
        return $this->doPost("/api/v2/mix/account/set-leverage", $params);
    }

    public function doGet($url, $body)
    {
        $url = $url . self::buildGetParams($body);
//        print_r($url . " ======= url ======\n");
        $requestUrl = self::restApiUrl . $url;
        $headerArray = $this->getHead(self::GET, $url, null);
        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, $requestUrl);
        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, FALSE);
        curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, FALSE);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
        curl_setopt($ch, CURLOPT_HTTPHEADER, $headerArray);
        curl_setopt($ch, CURLOPT_TIMEOUT, 10);

        $output = curl_exec($ch);
        curl_close($ch);
        return $output;
    }

    function doPost($url, $data)
    {
        $data = json_encode($data);
        $requestUrl = self::restApiUrl . $url;
        $headerArray = $this->getHead(self::POST, $url, $data);
        $curl = curl_init();
        curl_setopt($curl, CURLOPT_URL, $requestUrl);
        curl_setopt($curl, CURLOPT_SSL_VERIFYPEER, FALSE);
        curl_setopt($curl, CURLOPT_SSL_VERIFYHOST, FALSE);
        curl_setopt($curl, CURLOPT_POST, 1);
        curl_setopt($curl, CURLOPT_POSTFIELDS, $data);
        curl_setopt($curl, CURLOPT_HTTPHEADER, $headerArray);
        curl_setopt($curl, CURLOPT_RETURNTRANSFER, 1);
        curl_setopt($curl, CURLOPT_TIMEOUT, 10);
        $output = curl_exec($curl);
        curl_close($curl);
        return $output;
    }

    function getHead($method, $requestPath, $body)
    {
        $timestamp = self::getTimestamp();
        $sign = self::getSign($timestamp, $method, $requestPath, $body, $this->apiSecret);
        if (self::signType == "RSA") {
            $sign = self::getSignByRSA($timestamp, $method, $requestPath, $body, $this->apiSecret);
        }

        $headerArray = array();
        $headerArray[0] = "Content-type:application/json;";
        $headerArray[1] = "ACCESS-KEY:" . $this->apiKey;
        $headerArray[2] = "ACCESS-SIGN:" . $sign;
        $headerArray[3] = "ACCESS-TIMESTAMP:" . (string)$timestamp;
        $headerArray[4] = "ACCESS-PASSPHRASE:" . $this->passphrase;
        return $headerArray;
    }

    function buildGetParams($para)
    {
        $arg = "";

        if ($para == null || $para == "") {
            return $arg;
        }


//        $x = 0;
//        foreach ($para as $key => $value) {
//            if ($value == "" || $value == null) {
//                continue;
//            }
//
//            $param = $key . "=" . $value;
//            if ($x == 0) {
//                $arg = "?" . $arg . $param;
//            } else {
//                $arg = $arg . "&" . $param;
//            }
//            $x = $x + 1;
//        }

        $arg = self::sort_data($para);
        if ($arg == null || $arg == "") {
            return "";
        }
        return "?".$arg;
    }

    function sort_data($data){
        ksort($data);
        $result = [];
        foreach ($data as $key => $val) {
//            array_push($result, $key."=".urlencode($val));
            array_push($result, $key."=".$val);
        }
        return join("&", $result);
    }

    public static function getSign($timestamp, $method, $requestPath, $body, $apiSecret): string
    {
        if ($body != null) {
            $message = (string)$timestamp . strtoupper($method) . $requestPath . (string)$body;
        } else {
            $message = (string)$timestamp . strtoupper($method) . $requestPath;
        }

        return base64_encode(hash_hmac('sha256', $message, $apiSecret, true));
    }

    public static function getSignByRSA($timestamp, $method, $requestPath, $body, $apiSecret):string
    {
        $rsaUtil = new RsaUtil(null, $apiSecret);
        if ($body != null) {
            $message = (string)$timestamp . strtoupper($method) . $requestPath . (string)$body;
        } else {
            $message = (string)$timestamp . strtoupper($method) . $requestPath;
        }

//        print_r("fuck rsa\n");
        print_r($rsaUtil->sign($message) . "\n");
        return $rsaUtil->sign($message);
    }

    // 获取IOS格式时间戳
    public static function getTimestamp(): int
    {
        return time() * 1000;
    }

    public static function printLog(string $msg, string $type): void
    {
        $time = date("Y-m-d H:i:s");
        print_r("[" . $time . "] [" . $type . "] " . $msg . "\n");
    }
}