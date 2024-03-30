import { BaseApi } from '../BaseApi';
export declare class SpotMarketApi extends BaseApi {
    coins(qsOrBody: object): Promise<import("axios").AxiosResponse<any>>;
    symbols(qsOrBody: object): Promise<import("axios").AxiosResponse<any>>;
    fills(qsOrBody: object): Promise<import("axios").AxiosResponse<any>>;
    orderbook(qsOrBody: object): Promise<import("axios").AxiosResponse<any>>;
    tickers(qsOrBody: object): Promise<import("axios").AxiosResponse<any>>;
    candles(qsOrBody: object): Promise<import("axios").AxiosResponse<any>>;
}
