"use strict";
var __createBinding = (this && this.__createBinding) || (Object.create ? (function(o, m, k, k2) {
    if (k2 === undefined) k2 = k;
    Object.defineProperty(o, k2, { enumerable: true, get: function() { return m[k]; } });
}) : (function(o, m, k, k2) {
    if (k2 === undefined) k2 = k;
    o[k2] = m[k];
}));
var __setModuleDefault = (this && this.__setModuleDefault) || (Object.create ? (function(o, v) {
    Object.defineProperty(o, "default", { enumerable: true, value: v });
}) : function(o, v) {
    o["default"] = v;
});
var __importStar = (this && this.__importStar) || function (mod) {
    if (mod && mod.__esModule) return mod;
    var result = {};
    if (mod != null) for (var k in mod) if (k !== "default" && Object.hasOwnProperty.call(mod, k)) __createBinding(result, mod, k);
    __setModuleDefault(result, mod);
    return result;
};
var __importDefault = (this && this.__importDefault) || function (mod) {
    return (mod && mod.__esModule) ? mod : { "default": mod };
};
Object.defineProperty(exports, "__esModule", { value: true });
exports.BitgetWsClient = exports.Listenner = void 0;
const events_1 = require("events");
const util_1 = require("../util");
const config_1 = require("../config");
const ws_1 = __importDefault(require("ws"));
const Console = __importStar(require("console"));
const WsLoginReq_1 = require("../model/ws/WsLoginReq");
const WsBaseReq_1 = require("../model/ws/WsBaseReq");
const contant_1 = require("../contant");
class Listenner {
}
exports.Listenner = Listenner;
class BitgetWsClient extends events_1.EventEmitter {
    constructor(callBack, apiKey, apiSecret, passphrase) {
        super();
        this.websocketUri = config_1.API_CONFIG.WS_URL;
        this.callBack = callBack;
        this.socket = new ws_1.default(config_1.API_CONFIG.WS_URL, {});
        this.apiKey = apiKey;
        this.apiSecret = apiSecret;
        this.passphrase = passphrase;
        this.socket.on('open', () => this.onOpen());
        this.socket.on('close', (code, reason) => this.onClose(code, reason));
        this.socket.on('message', data => this.onMessage(data));
    }
    login() {
        const timestamp = Math.floor(Date.now() / 1000);
        let sign = util_1.encrypt('GET', '/user/verify', null, timestamp, this.apiSecret);
        if (config_1.API_CONFIG.SIGN_TYPE === contant_1.BIZ_CONSTANT.RSA) {
            sign = util_1.encryptRSA('GET', '/user/verify', null, timestamp, this.apiSecret);
        }
        const wsLoginReq = new WsLoginReq_1.WsLoginReq(this.apiKey, this.passphrase, timestamp.toString(), sign);
        const args = new Array();
        args.push(wsLoginReq);
        const request = new WsBaseReq_1.WsBaseReq('login', args);
        this.send(request);
    }
    subscribe(args) {
        const request = new WsBaseReq_1.WsBaseReq('subscribe', args);
        this.send(request);
    }
    unsubscribe(args) {
        const request = new WsBaseReq_1.WsBaseReq('unsubscribe', args);
        this.send(request);
    }
    send(messageObject) {
        const that = this;
        if (!this.socket)
            throw Error('socket is not open');
        const jsonStr = util_1.toJsonString(messageObject);
        Console.info('sendInfo:' + jsonStr);
        setInterval(() => {
            var _a;
            if (that.isOpen) {
                (_a = this.socket) === null || _a === void 0 ? void 0 : _a.send(jsonStr);
            }
        }, 1000);
    }
    onOpen() {
        this.isOpen = true;
        Console.info(`on open Connected to ${this.websocketUri}`);
        this.initTimer();
        this.emit('open');
    }
    initTimer() {
        this.interval = setInterval(() => {
            if (this.socket) {
                this.socket.send('ping');
            }
        }, 5000);
    }
    onMessage(data) {
        var _a;
        if (typeof data === 'string') {
            (_a = this.callBack) === null || _a === void 0 ? void 0 : _a.reveice(data);
        }
    }
    onClose(code, reason) {
        Console.log(`Websocket connection is closed.code=${code},reason=${reason}`);
        this.socket = undefined;
        if (this.interval) {
            clearInterval(this.interval);
            this.interval = null;
        }
        this.emit('close');
    }
    close() {
        if (this.socket) {
            Console.log(`Closing websocket connection...`);
            this.socket.close();
            if (this.interval) {
                clearInterval(this.interval);
                this.interval = null;
            }
            this.socket = undefined;
        }
    }
}
exports.BitgetWsClient = BitgetWsClient;
//# sourceMappingURL=data:application/json;base64,eyJ2ZXJzaW9uIjozLCJmaWxlIjoiQml0Z2V0V3NDbGllbnQuanMiLCJzb3VyY2VSb290IjoiIiwic291cmNlcyI6WyIuLi8uLi8uLi9zcmMvbGliL3dzL0JpdGdldFdzQ2xpZW50LnRzIl0sIm5hbWVzIjpbXSwibWFwcGluZ3MiOiI7Ozs7Ozs7Ozs7Ozs7Ozs7Ozs7Ozs7Ozs7QUFBQSxtQ0FBb0M7QUFDcEMsa0NBQTBEO0FBQzFELHNDQUFxQztBQUNyQyw0Q0FBMkI7QUFDM0IsaURBQW1DO0FBQ25DLHVEQUFrRDtBQUNsRCxxREFBZ0Q7QUFFaEQsd0NBQXdDO0FBRXhDLE1BQXNCLFNBQVM7Q0FFOUI7QUFGRCw4QkFFQztBQUVELE1BQWEsY0FBZSxTQUFRLHFCQUFZO0lBVTVDLFlBQVksUUFBa0IsRUFBQyxNQUFjLEVBQUUsU0FBaUIsRUFBRSxVQUFrQjtRQUNoRixLQUFLLEVBQUUsQ0FBQztRQUNSLElBQUksQ0FBQyxZQUFZLEdBQUcsbUJBQVUsQ0FBQyxNQUFNLENBQUM7UUFDdEMsSUFBSSxDQUFDLFFBQVEsR0FBRyxRQUFRLENBQUM7UUFDekIsSUFBSSxDQUFDLE1BQU0sR0FBRyxJQUFJLFlBQVMsQ0FBQyxtQkFBVSxDQUFDLE1BQU0sRUFBRSxFQUFFLENBQUMsQ0FBQztRQUNuRCxJQUFJLENBQUMsTUFBTSxHQUFHLE1BQU0sQ0FBQztRQUNyQixJQUFJLENBQUMsU0FBUyxHQUFHLFNBQVMsQ0FBQztRQUMzQixJQUFJLENBQUMsVUFBVSxHQUFHLFVBQVUsQ0FBQztRQUU3QixJQUFJLENBQUMsTUFBTSxDQUFDLEVBQUUsQ0FBQyxNQUFNLEVBQUUsR0FBRyxFQUFFLENBQUMsSUFBSSxDQUFDLE1BQU0sRUFBRSxDQUFDLENBQUM7UUFDNUMsSUFBSSxDQUFDLE1BQU0sQ0FBQyxFQUFFLENBQUMsT0FBTyxFQUFFLENBQUMsSUFBSSxFQUFFLE1BQU0sRUFBRSxFQUFFLENBQUMsSUFBSSxDQUFDLE9BQU8sQ0FBQyxJQUFJLEVBQUUsTUFBTSxDQUFDLENBQUMsQ0FBQztRQUN0RSxJQUFJLENBQUMsTUFBTSxD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