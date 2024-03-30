export declare class Config {
    private _apiUri;
    private _apiKey;
    private _secretKey;
    private _passphrase;
    constructor(apiUri: string, apiKey: string, secretKey: string, passphrase: string);
    get apiUri(): string;
    set apiUri(value: string);
    get apiKey(): string;
    set apiKey(value: string);
    get secretKey(): string;
    set secretKey(value: string);
    get passphrase(): string;
    set passphrase(value: string);
}
