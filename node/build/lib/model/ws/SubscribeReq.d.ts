export declare class SubscribeReq {
    private _instType;
    private _channel;
    private _instId;
    private _coin;
    constructor(instType: string, channel: string, instId: string);
    get instType(): string;
    set instType(value: string);
    get channel(): string;
    set channel(value: string);
    get instId(): string;
    set instId(value: string);
    get coin(): string;
    set coin(value: string);
}
