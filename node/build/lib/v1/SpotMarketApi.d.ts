import { BaseApi } from '../BaseApi';
export declare class SpotMarketApi extends BaseApi {
    currencies(): Promise<import("axios").AxiosResponse<any>>;
    products(): Promise<import("axios").AxiosResponse<any>>;
    product(qsOrBody: object): Promise<import("axios").AxiosResponse<any>>;
    fills(qsOrBody: object): Promise<import("axios").AxiosResponse<any>>;
    depth(qsOrBody: object): Promise<import("axios").AxiosResponse<any>>;
    ticker(qsOrBody: object): Promise<import("axios").AxiosResponse<any>>;
    tickers(): Promise<import("axios").AxiosResponse<any>>;
    candles(qsOrBody: object): Promise<import("axios").AxiosResponse<any>>;
}
