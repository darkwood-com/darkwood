import { BaseApi } from '../BaseApi';
export declare class SpotAccountApi extends BaseApi {
    getInfo(): Promise<import("axios").AxiosResponse<any>>;
    assetsLite(qsOrBody: object): Promise<import("axios").AxiosResponse<any>>;
    bills(qsOrBody: object): Promise<import("axios").AxiosResponse<any>>;
    transferRecords(qsOrBody: object): Promise<import("axios").AxiosResponse<any>>;
}
