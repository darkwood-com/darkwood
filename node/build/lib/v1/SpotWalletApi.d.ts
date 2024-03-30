import { BaseApi } from '../BaseApi';
export declare class SpotWalletApi extends BaseApi {
    transfer(qsOrBody: object): Promise<import("axios").AxiosResponse<any>>;
    depositAddress(qsOrBody: object): Promise<import("axios").AxiosResponse<any>>;
    withdrawal(qsOrBody: object): Promise<import("axios").AxiosResponse<any>>;
    withdrawalRecords(qsOrBody: object): Promise<import("axios").AxiosResponse<any>>;
    depositRecords(qsOrBody: object): Promise<import("axios").AxiosResponse<any>>;
}
