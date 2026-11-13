import "../app/globals.css";
import { useEffect } from "react";
import axios from 'axios';
import Image from "next/image";
import { MixMarketApi } from "@/lib/v2/MixMarketApi";
import { BitgetApi } from "@/lib/BitgetApi";

export default function Index() {

  /*let api = new MixMarketApi(
    process.env.NEXT_PUBLIC_BITGET_API_KEY ?? '',
    process.env.NEXT_PUBLIC_BITGET_API_SECRET ?? '',
    process.env.NEXT_PUBLIC_BITGET_API_PHRASE ?? '',
  )
  const qsOrBody = {'productType': 'umcbl'};
  api.ticker(qsOrBody);*/

  class ListennerObj extends BitgetApi.Listenner{
      reveice(message){
          Console.info('>>>'+message);
      }
  }

  const listenner = new ListennerObj();
  const bitgetWsClient = new BitgetApi.BitgetWsClient(listenner,apiKey,secretKey,passphrase);
  const subArr = new Array();

  const subscribeOne = new BitgetApi.SubscribeReq('mc','ticker','BTCUSD');
  const subscribeTow = new BitgetApi.SubscribeReq('SP','candle1W','BTCUSDT');

  subArr.push(subscribeOne);
  subArr.push(subscribeTow);

  bitgetWsClient.subscribe(subArr)

  return <div>Main</div>
}