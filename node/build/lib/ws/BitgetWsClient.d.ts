/// <reference types="node" />
import { EventEmitter } from 'events';
import { SubscribeReq } from '../model/ws/SubscribeReq';
export declare abstract class Listenner {
    abstract reveice(message: string): void;
}
export declare class BitgetWsClient extends EventEmitter {
    private websocketUri;
    private socket?;
    private interval?;
    private isOpen?;
    private callBack?;
    private apiKey;
    private apiSecret;
    private passphrase;
    constructor(callBack: Listenner, apiKey: string, apiSecret: string, passphrase: string);
    login(): void;
    subscribe(args: SubscribeReq[]): void;
    unsubscribe(args: SubscribeReq[]): void;
    private send;
    private onOpen;
    private initTimer;
    private onMessage;
    private onClose;
    close(): void;
}
