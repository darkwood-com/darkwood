import { BaseApi } from '../BaseApi';
export declare class MixMarketApi extends BaseApi {
    contracts(qsOrBody: object): Promise<import("axios").AxiosResponse<any>>;
    orderbook(qsOrBody: object): Promise<import("axios").AxiosResponse<any>>;
    ticker(qsOrBody: object): Promise<import("axios").AxiosResponse<any>>;
    tickers(qsOrBody: object): Promise<import("axios").AxiosResponse<any>>;
    fills(qsOrBody: object): Promise<import("axios").AxiosResponse<any>>;
    candles(qsOrBody: object): Promise<import("axios").AxiosResponse<any>>;
}
