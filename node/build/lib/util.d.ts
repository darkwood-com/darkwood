/// <reference types="node" />
export interface BitgetApiHeader {
    'ACCESS-SIGN': string;
    'ACCESS-TIMESTAMP': number | string;
    'ACCESS-KEY': string;
    'ACCESS-PASSPHRASE': string;
    'Content-Type': string;
    Cookie: string;
    locale: string;
}
/**
 * 获取签名器
 * @param apiKey
 * @param secretKey
 * @param timestamp
 * @param passphrase
 */
export default function getSigner(apiKey?: string, secretKey?: string, passphrase?: string): (httpMethod: string, url: string, qsOrBody: NodeJS.Dict<any> | null, locale?: string) => {
    'ACCESS-SIGN': string;
    'ACCESS-TIMESTAMP': number;
    'ACCESS-KEY': string;
    'ACCESS-PASSPHRASE': string;
    'Content-Type': string;
    Cookie: string;
    locale: string;
};
/**
 * 加密算法
 * @param httpMethod
 * @param url
 * @param qsOrBody
 * @param timestamp
 * @param secretKey
 */
export declare function encrypt(httpMethod: string, url: string, qsOrBody: NodeJS.Dict<string | number> | null, timestamp: number, secretKey: string): string;
export declare function toJsonString(obj: object): string | null;
/**
 * RSA加密算法
 * @param httpMethod
 * @param url
 * @param qsOrBody
 * @param timestamp
 * @param secretKey
 */
export declare function encryptRSA(httpMethod: string, url: string, qsOrBody: NodeJS.Dict<string | number> | null, timestamp: number, secretKey: string): any;
export declare function sortByKey(dict: NodeJS.Dict<string | number>): any;
