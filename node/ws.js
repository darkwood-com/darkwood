var bitgetApi = require("bitget-openapi")
var Console = require("console")

const apiKey = '';
const secretKey = '';
const passphrase = '';
//处理消息的实现类
class ListennerObj extends bitgetApi.Listenner{
    reveice(message){
        Console.info('>>>'+message);
    }
}

const listenner = new ListennerObj();
const bitgetWsClient = new bitgetApi.BitgetWsClient(listenner,apiKey,secretKey,passphrase);
const subArr = new Array();

const subscribeOne = new bitgetApi.SubscribeReq('mc','ticker','BTCUSD');
const subscribeTow = new bitgetApi.SubscribeReq('SP','candle1W','BTCUSDT');

subArr.push(subscribeOne);
subArr.push(subscribeTow);

bitgetWsClient.subscribe(subArr)