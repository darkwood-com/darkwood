/// <reference types="node" />
import { BitgetApiHeader } from './util';
import { AxiosInstance, AxiosRequestConfig } from 'axios';
export declare class BaseApi {
    protected signer: (httpMethod: string, url: string, qsOrBody: NodeJS.Dict<any> | null, locale?: string) => BitgetApiHeader;
    constructor(apiKey: string, secretKey: string, passphrase: string, httpConfig?: AxiosRequestConfig);
    axiosInstance: AxiosInstance;
}
