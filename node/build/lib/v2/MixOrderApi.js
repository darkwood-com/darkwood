"use strict";
Object.defineProperty(exports, "__esModule", { value: true });
exports.MixOrderApi = void 0;
const BaseApi_1 = require("../BaseApi");
class MixOrderApi extends BaseApi_1.BaseApi {
    placeOrder(qsOrBody) {
        const url = '/api/v2/mix/order/place-order';
        const headers = this.signer('POST', url, qsOrBody);
        return this.axiosInstance.post(url, qsOrBody, { headers });
    }
    batchPlaceOrder(qsOrBody) {
        const url = '/api/v2/mix/order/batch-place-order';
        const headers = this.signer('POST', url, qsOrBody);
        return this.axiosInstance.post(url, qsOrBody, { headers });
    }
    cancelOrder(qsOrBody) {
        const url = '/api/v2/mix/order/cancel-order';
        const headers = this.signer('POST', url, qsOrBody);
        return this.axiosInstance.post(url, qsOrBody, { headers });
    }
    batchCancelOrders(qsOrBody) {
        const url = '/api/v2/mix/order/batch-cancel-orders';
        const headers = this.signer('POST', url, qsOrBody);
        return this.axiosInstance.post(url, qsOrBody, { headers });
    }
    ordersPending(qsOrBody) {
        const url = '/api/v2/mix/order/orders-pending';
        const headers = this.signer('GET', url, qsOrBody);
        return this.axiosInstance.get(url, { headers, params: qsOrBody });
    }
    ordersHistory(qsOrBody) {
        const url = '/api/v2/mix/order/orders-history';
        const headers = this.signer('GET', url, qsOrBody);
        return this.axiosInstance.get(url, { headers, params: qsOrBody });
    }
    fills(qsOrBody) {
        const url = '/api/v2/mix/order/fills';
        const headers = this.signer('GET', url, qsOrBody);
        return this.axiosInstance.get(url, { headers, params: qsOrBody });
    }
    placePlanOrder(qsOrBody) {
        const url = '/api/v2/mix/order/place-plan-order';
        const headers = this.signer('POST', url, qsOrBody);
        return this.axiosInstance.post(url, qsOrBody, { headers });
    }
    cancelPlanOrder(qsOrBody) {
        const url = '/api/v2/mix/order/cancel-plan-order';
        const headers = this.signer('POST', url, qsOrBody);
        return this.axiosInstance.post(url, qsOrBody, { headers });
    }
    ordersPlanPending(qsOrBody) {
        const url = '/api/v2/mix/order/orders-plan-pending';
        const headers = this.signer('GET', url, qsOrBody);
        return this.axiosInstance.get(url, { headers, params: qsOrBody });
    }
    ordersPlanHistory(qsOrBody) {
        const url = '/api/v2/mix/order/orders-plan-history';
        const headers = this.signer('GET', url, qsOrBody);
        return this.axiosInstance.get(url, { headers, params: qsOrBody });
    }
    traderOrderClosePositions(qsOrBody) {
        const url = '/api/v2/copy/mix-trader/order-close-positions';
        const headers = this.signer('POST', url, qsOrBody);
        return this.axiosInstance.post(url, qsOrBody, { headers });
    }
    traderOrderCurrentTrack(qsOrBody) {
        const url = '/api/v2/copy/mix-trader/order-current-track';
        const headers = this.signer('GET', url, qsOrBody);
        return this.axiosInstance.get(url, { headers, params: qsOrBody });
    }
    traderOrderHistoryTrack(qsOrBody) {
        const url = '/api/v2/copy/mix-trader/order-history-track';
        const headers = this.signer('GET', url, qsOrBody);
        return this.axiosInstance.get(url, { headers, params: qsOrBody });
    }
    followerClosePositions(qsOrBody) {
        const url = '/api/v2/copy/mix-follower/close-positions';
        const headers = this.signer('POST', url, qsOrBody);
        return this.axiosInstance.post(url, qsOrBody, { headers });
    }
    followerQueryCurrentOrders(qsOrBody) {
        const url = '/api/v2/copy/mix-follower/query-current-orders';
        const headers = this.signer('GET', url, qsOrBody);
        return this.axiosInstance.get(url, { headers, params: qsOrBody });
    }
    followerQueryHistoryOrders(qsOrBody) {
        const url = '/api/v2/copy/mix-follower/query-history-orders';
        const headers = this.signer('GET', url, qsOrBody);
        return this.axiosInstance.get(url, { headers, params: qsOrBody });
    }
}
exports.MixOrderApi = MixOrderApi;
//# sourceMappingURL=data:application/json;base64,eyJ2ZXJzaW9uIjozLCJmaWxlIjoiTWl4T3JkZXJBcGkuanMiLCJzb3VyY2VSb290IjoiIiwic291cmNlcyI6WyIuLi8uLi8uLi9zcmMvbGliL3YyL01peE9yZGVyQXBpLnRzIl0sIm5hbWVzIjpbXSwibWFwcGluZ3MiOiI7OztBQUFBLHdDQUFtQztBQUVuQyxNQUFhLFdBQVksU0FBUSxpQkFBTztJQUVwQyxVQUFVLENBQUMsUUFBZ0I7UUFDdkIsTUFBTSxHQUFHLEdBQUcsK0JBQStCLENBQUM7UUFDNUMsTUFBTSxPQUFPLEdBQUcsSUFBSSxDQUFDLE1BQU0sQ0FBQyxNQUFNLEVBQUUsR0FBRyxFQUFFLFFBQVEsQ0FBQyxDQUFBO1FBQ2xELE9BQU8sSUFBSSxDQUFDLGFBQWEsQ0FBQyxJQUFJLENBQUMsR0FBRyxFQUFFLFFBQVEsRUFBRSxFQUFDLE9BQU8sRUFBQyxDQUFDLENBQUE7SUFDNUQsQ0FBQztJQUVELGVBQWUsQ0FBQyxRQUFnQjtRQUM1QixNQUFNLEdBQUcsR0FBRyxxQ0FBcUMsQ0FBQztRQUNsRCxNQUFNLE9BQU8sR0FBRyxJQUFJLENBQUMsTUFBTSxDQUFDLE1BQU0sRUFBRSxHQUFHLEVBQUUsUUFBUSxDQUFDLENBQUE7UUFDbEQsT0FBTyxJQUFJLENBQUMsYUFBYSxDQUFDLElBQUksQ0FBQyxHQUFHLEVBQUUsUUFBUSxFQUFFLEVBQUMsT0FBTyxFQUFDLENBQUMsQ0FBQTtJQUM1RCxDQUFDO0lBRUQsV0FBVyxDQUFDLFFBQWdCO1FBQ3hCLE1BQU0sR0FBRyxHQUFHLGdDQUFnQyxDQUFDO1FBQzdDLE1BQU0sT0FBTyxHQUFHLElBQUksQ0FBQyxNQUFNLENBQUMsTUFBTSxFQUFFLEdBQUcsRUFBRSxRQUFRLENBQUMsQ0FBQTtRQUNsRCxPQUFPLElBQUksQ0FBQyxhQUFhLENBQUMsSUFBSSxDQUFDLEdBQUcsRUFBRSxRQUFRLEVBQUUsRUFBQyxPQUFPLEVBQUMsQ0FBQyxDQUFBO0lBQzVELENBQUM7SUFFRCxpQkFBaUIsQ0FBQyxRQUFnQjtRQUM5QixNQUFNLEdBQUcsR0FBRyx1Q0FBdUMs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