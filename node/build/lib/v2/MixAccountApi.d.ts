import { BaseApi } from '../BaseApi';
export declare class MixAccountApi extends BaseApi {
    account(qsOrBody: object): Promise<import("axios").AxiosResponse<any>>;
    accounts(qsOrBody: object): Promise<import("axios").AxiosResponse<any>>;
    setLeverage(qsOrBody: object): Promise<import("axios").AxiosResponse<any>>;
    setMargin(qsOrBody: object): Promise<import("axios").AxiosResponse<any>>;
    setMarginMode(qsOrBody: object): Promise<import("axios").AxiosResponse<any>>;
    setPositionMode(qsOrBody: object): Promise<import("axios").AxiosResponse<any>>;
    singlePosition(qsOrBody: object): Promise<import("axios").AxiosResponse<any>>;
    allPosition(qsOrBody: object): Promise<import("axios").AxiosResponse<any>>;
}
