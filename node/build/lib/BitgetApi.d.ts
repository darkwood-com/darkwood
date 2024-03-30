import { BaseApi } from './BaseApi';
export declare class BitgetApi extends BaseApi {
    get(url: string, qsOrBody: object): Promise<import("axios").AxiosResponse<any>>;
    post(url: string, qsOrBody: object): Promise<import("axios").AxiosResponse<any>>;
}
