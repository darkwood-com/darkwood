import { BaseApi } from '../BaseApi';
export declare class SpotAccountApi extends BaseApi {
    info(): Promise<import("axios").AxiosResponse<any>>;
    assets(qsOrBody: object): Promise<import("axios").AxiosResponse<any>>;
    bills(qsOrBody: object): Promise<import("axios").AxiosResponse<any>>;
    transferRecords(qsOrBody: object): Promise<import("axios").AxiosResponse<any>>;
}
