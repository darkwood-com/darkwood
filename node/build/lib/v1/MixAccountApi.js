"use strict";
Object.defineProperty(exports, "__esModule", { value: true });
exports.MixAccountApi = void 0;
const BaseApi_1 = require("../BaseApi");
class MixAccountApi extends BaseApi_1.BaseApi {
    account(qsOrBody) {
        const url = '/api/mix/v1/account/account';
        const headers = this.signer('GET', url, qsOrBody);
        return this.axiosInstance.get(url, { headers, params: qsOrBody });
    }
    accounts(qsOrBody) {
        const url = '/api/mix/v1/account/accounts';
        const headers = this.signer('GET', url, qsOrBody);
        return this.axiosInstance.get(url, { headers, params: qsOrBody });
    }
    setLeverage(qsOrBody) {
        const url = '/api/mix/v1/account/setLeverage';
        const headers = this.signer('POST', url, qsOrBody);
        return this.axiosInstance.post(url, qsOrBody, { headers });
    }
    setMargin(qsOrBody) {
        const url = '/api/mix/v1/account/setMargin';
        const headers = this.signer('POST', url, qsOrBody);
        return this.axiosInstance.post(url, qsOrBody, { headers });
    }
    setMarginMode(qsOrBody) {
        const url = '/api/mix/v1/account/setMarginMode';
        const headers = this.signer('POST', url, qsOrBody);
        return this.axiosInstance.post(url, qsOrBody, { headers });
    }
    setPositionMode(qsOrBody) {
        const url = '/api/mix/v1/account/setPositionMode';
        const headers = this.signer('POST', url, qsOrBody);
        return this.axiosInstance.post(url, qsOrBody, { headers });
    }
    singlePosition(qsOrBody) {
        const url = '/api/mix/v1/position/singlePosition';
        const headers = this.signer('GET', url, qsOrBody);
        return this.axiosInstance.get(url, { headers, params: qsOrBody });
    }
    allPosition(qsOrBody) {
        const url = '/api/mix/v1/position/allPosition';
        const headers = this.signer('GET', url, qsOrBody);
        return this.axiosInstance.get(url, { headers, params: qsOrBody });
    }
}
exports.MixAccountApi = MixAccountApi;
//# sourceMappingURL=data:application/json;base64,eyJ2ZXJzaW9uIjozLCJmaWxlIjoiTWl4QWNjb3VudEFwaS5qcyIsInNvdXJjZVJvb3QiOiIiLCJzb3VyY2VzIjpbIi4uLy4uLy4uL3NyYy9saWIvdjEvTWl4QWNjb3VudEFwaS50cyJdLCJuYW1lcyI6W10sIm1hcHBpbmdzIjoiOzs7QUFBQSx3Q0FBbUM7QUFFbkMsTUFBYSxhQUFjLFNBQVEsaUJBQU87SUFFdEMsT0FBTyxDQUFDLFFBQWdCO1FBQ3BCLE1BQU0sR0FBRyxHQUFHLDZCQUE2QixDQUFDO1FBQzFDLE1BQU0sT0FBTyxHQUFHLElBQUksQ0FBQyxNQUFNLENBQUMsS0FBSyxFQUFFLEdBQUcsRUFBRSxRQUFRLENBQUMsQ0FBQTtRQUNqRCxPQUFPLElBQUksQ0FBQyxhQUFhLENBQUMsR0FBRyxDQUFDLEdBQUcsRUFBRSxFQUFDLE9BQU8sRUFBRSxNQUFNLEVBQUUsUUFBUSxFQUFDLENBQUMsQ0FBQTtJQUNuRSxDQUFDO0lBRUQsUUFBUSxDQUFDLFFBQWdCO1FBQ3JCLE1BQU0sR0FBRyxHQUFHLDhCQUE4QixDQUFDO1FBQzNDLE1BQU0sT0FBTyxHQUFHLElBQUksQ0FBQyxNQUFNLENBQUMsS0FBSyxFQUFFLEdBQUcsRUFBRSxRQUFRLENBQUMsQ0FBQTtRQUNqRCxPQUFPLElBQUksQ0FBQyxhQUFhLENBQUMsR0FBRyxDQUFDLEdBQUcsRUFBRSxFQUFDLE9BQU8sRUFBRSxNQUFNLEVBQUUsUUFBUSxFQUFDLENBQUMsQ0FBQTtJQUNuRSxDQUFDO0lBRUQsV0FBVyxDQUFDLFFBQWdCO1FBQ3hCLE1BQU0sR0FBRyxHQUFHLGlDQUFpQyxDQUFDO1FBQzlDLE1BQU0sT0FBTyxHQUFHLElBQUksQ0FBQyxNQUFNLENBQUMsTUFBTSxFQUFFLEdBQUcsRUFBRSxRQUFRLENBQUMsQ0FBQTtRQUNsRCxPQUFPLElBQUksQ0FBQyxhQUFhLENBQUMsSUFBSSxDQUFDLEdBQUcsRUFBRSxRQUFRLEVBQUUsRUFBQyxPQUFPLEVBQUMsQ0FBQyxDQUFBO0lBQzVELENBQUM7SUFFRCxTQUFTLENBQUMsUUFBZ0I7UUFDdEIsTUFBTSxHQUFHLEdBQUcsK0JBQStCLENBQUM7UUFDNUMsTUFBTSxPQUFPLEdBQUcsSUFBSSxDQUFDLE1BQU0sQ0FBQyxNQUFNLEVBQUUsR0FBRyxFQUFFLFFBQVEsQ0FBQyxDQUFBO1FBQ2xELE9BQU8sSUFBSSxDQUFDLGFBQWEsQ0FBQyxJQUFJLENBQUMsR0FBRyxFQUFFLFFBQVEsRUFBRSxFQUFDLE9BQU8sRUFBQyxDQUFDLENBQUE7SUFDNUQsQ0FBQztJQUVELGFBQWEsQ0FBQyxRQUFnQjtRQUMxQixNQUFNLEdBQUcsR0FBRyxtQ0FBbUMsQ0FBQztRQUNoRCxNQUFNLE9BQU8sR0FBRyxJQUFJLENBQUMsTUFBTSxDQUFDLE1BQU0sRUFBRSxHQUFHLEVBQUUsUUFBUSxDQUFDLENBQUE7UUFDbEQsT0FBTyxJQUFJLENBQUMsYUFBYSxDQUFDLElBQUksQ0FBQyxHQUFHLEVBQUUsUUFBUSxFQUFFLEVBQUMsT0FBTyxFQUFDLENBQUMsQ0FBQTtJQUM1RCxDQUFDO0lBRUQsZUFBZSxDQUFDLFFBQWdCO1FBQzVCLE1BQU0sR0FBRyxHQUFHLHFDQUFxQyxDQUFDO1FBQ2xELE1BQU0sT0FBTyxHQUFHLElBQUksQ0FBQyxNQUFNLENBQUMsTUFBTSxFQUFFLEdBQUcsRUFBRSxRQUFRLENBQUMsQ0FBQTtRQUNsRCxPQUFPLElBQUksQ0FBQyxhQUFhLENBQUMsSUFBSSxDQUFDLEdBQUcsRUFBRSxRQUFRLEVBQUUsRUFBQyxPQUFPLEVBQUMsQ0FBQyxDQUFBO0lBQzVELENBQUM7SUFFRCxjQUFjLENBQUMsUUFBZ0I7UUFDM0IsTUFBTSxHQUFHLEdBQUcscUNBQXFDLENBQUM7UUFDbEQsTUFBTSxPQUFPLEdBQUcsSUFBSSxDQUFDLE1BQU0sQ0FBQyxLQUFLLEVBQUUsR0FBRyxFQUFFLFFBQVEsQ0FBQyxDQUFBO1FBQ2pELE9BQU8sSUFBSSxDQUFDLGFBQWEsQ0FBQyxHQUFHLENBQUMsR0FBRyxFQUFFLEVBQUMsT0FBTyxFQUFFLE1BQU0sRUFBRSxRQUFRLEVBQUMsQ0FBQyxDQUFBO0lBQ25FLENBQUM7SUFFRCxXQUFXLENBQUMsUUFBZ0I7UUFDeEIsTUFBTSxHQUFHLEdBQUcsa0NBQWtDLENBQUM7UUFDL0MsTUFBTSxPQUFPLEdBQUcsSUFBSSxDQUFDLE1BQU0sQ0FBQyxLQUFLLEVBQUUsR0FBRyxFQUFFLFFBQVEsQ0FBQyxDQUFBO1FBQ2pELE9BQU8sSUFBSSxDQUFDLGFBQWEsQ0FBQyxHQUFHLENBQUMsR0FBRyxFQUFFLEVBQUMsT0FBTyxFQUFFLE1BQU0sRUFBRSxRQUFRLEVBQUMsQ0FBQyxDQUFBO0lBQ25FLENBQUM7Q0FFSjtBQWxERCxzQ0FrREMifQ==