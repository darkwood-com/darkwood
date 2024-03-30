import { BaseApi } from '../BaseApi';
export declare class SpotOrderApi extends BaseApi {
    orders(qsOrBody: object): Promise<import("axios").AxiosResponse<any>>;
    batchOrders(qsOrBody: object): Promise<import("axios").AxiosResponse<any>>;
    cancelOrder(qsOrBody: object): Promise<import("axios").AxiosResponse<any>>;
    cancelBatchOrder(qsOrBody: object): Promise<import("axios").AxiosResponse<any>>;
    openOrders(qsOrBody: object): Promise<import("axios").AxiosResponse<any>>;
    history(qsOrBody: object): Promise<import("axios").AxiosResponse<any>>;
    fills(qsOrBody: object): Promise<import("axios").AxiosResponse<any>>;
    placePlanOrder(qsOrBody: object): Promise<import("axios").AxiosResponse<any>>;
    cancelPlanOrder(qsOrBody: object): Promise<import("axios").AxiosResponse<any>>;
    currentPlanOrder(qsOrBody: object): Promise<import("axios").AxiosResponse<any>>;
    historyPlanOrder(qsOrBody: object): Promise<import("axios").AxiosResponse<any>>;
    traderOrderCloseTracking(qsOrBody: object): Promise<import("axios").AxiosResponse<any>>;
    traderOrderCurrentTrack(qsOrBody: object): Promise<import("axios").AxiosResponse<any>>;
    traderOrderHistoryTrack(qsOrBody: object): Promise<import("axios").AxiosResponse<any>>;
}
