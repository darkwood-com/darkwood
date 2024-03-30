export declare class WsBaseReq {
    private _op;
    private _args;
    constructor(op: string, args: object[]);
    get op(): string;
    set op(value: string);
    get args(): object[];
    set args(value: object[]);
}
