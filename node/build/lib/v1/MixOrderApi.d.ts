import { BaseApi } from '../BaseApi';
export declare class MixOrderApi extends BaseApi {
    placeOrder(qsOrBody: object): Promise<import("axios").AxiosResponse<any>>;
    batchPlaceOrder(qsOrBody: object): Promise<import("axios").AxiosResponse<any>>;
    cancelOrder(qsOrBody: object): Promise<import("axios").AxiosResponse<any>>;
    batchCancelOrders(qsOrBody: object): Promise<import("axios").AxiosResponse<any>>;
    ordersPending(qsOrBody: object): Promise<import("axios").AxiosResponse<any>>;
    ordersHistory(qsOrBody: object): Promise<import("axios").AxiosResponse<any>>;
    fills(qsOrBody: object): Promise<import("axios").AxiosResponse<any>>;
    placePlanOrder(qsOrBody: object): Promise<import("axios").AxiosResponse<any>>;
    cancelPlanOrder(qsOrBody: object): Promise<import("axios").AxiosResponse<any>>;
    ordersPlanPending(qsOrBody: object): Promise<import("axios").AxiosResponse<any>>;
    ordersPlanHistory(qsOrBody: object): Promise<import("axios").AxiosResponse<any>>;
    traderCloseOrder(qsOrBody: object): Promise<import("axios").AxiosResponse<any>>;
    traderCurrentOrders(qsOrBody: object): Promise<import("axios").AxiosResponse<any>>;
    traderHistoryTrack(qsOrBody: object): Promise<import("axios").AxiosResponse<any>>;
    followerCloseByTrackingNo(qsOrBody: object): Promise<import("axios").AxiosResponse<any>>;
    followerQueryCurrentOrders(qsOrBody: object): Promise<import("axios").AxiosResponse<any>>;
    followerQueryHistoryOrders(qsOrBody: object): Promise<import("axios").AxiosResponse<any>>;
}
