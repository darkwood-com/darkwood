"use strict";
Object.defineProperty(exports, "__esModule", { value: true });
exports.MixMarketApi = void 0;
const BaseApi_1 = require("../BaseApi");
class MixMarketApi extends BaseApi_1.BaseApi {
    contracts(qsOrBody) {
        const url = '/api/v2/mix/market/contracts';
        const headers = this.signer('GET', url, qsOrBody);
        return this.axiosInstance.get(url, { headers, params: qsOrBody });
    }
    orderbook(qsOrBody) {
        const url = '/api/v2/mix/market/orderbook';
        const headers = this.signer('GET', url, qsOrBody);
        return this.axiosInstance.get(url, { headers, params: qsOrBody });
    }
    ticker(qsOrBody) {
        const url = '/api/v2/mix/market/ticker';
        const headers = this.signer('GET', url, qsOrBody);
        return this.axiosInstance.get(url, { headers });
    }
    tickers(qsOrBody) {
        const url = '/api/v2/mix/market/tickers';
        const headers = this.signer('GET', url, qsOrBody);
        return this.axiosInstance.get(url, { headers });
    }
    fills(qsOrBody) {
        const url = '/api/v2/mix/market/fills';
        const headers = this.signer('GET', url, qsOrBody);
        return this.axiosInstance.get(url, { headers, params: qsOrBody });
    }
    candles(qsOrBody) {
        const url = '/api/v2/mix/market/candles';
        const headers = this.signer('GET', url, qsOrBody);
        return this.axiosInstance.get(url, { headers, params: qsOrBody });
    }
}
exports.MixMarketApi = MixMarketApi;
//# sourceMappingURL=data:application/json;base64,eyJ2ZXJzaW9uIjozLCJmaWxlIjoiTWl4TWFya2V0QXBpLmpzIiwic291cmNlUm9vdCI6IiIsInNvdXJjZXMiOlsiLi4vLi4vLi4vc3JjL2xpYi92Mi9NaXhNYXJrZXRBcGkudHMiXSwibmFtZXMiOltdLCJtYXBwaW5ncyI6Ijs7O0FBQUEsd0NBQW1DO0FBRW5DLE1BQWEsWUFBYSxTQUFRLGlCQUFPO0lBRXJDLFNBQVMsQ0FBQyxRQUFnQjtRQUN0QixNQUFNLEdBQUcsR0FBRyw4QkFBOEIsQ0FBQztRQUMzQyxNQUFNLE9BQU8sR0FBRyxJQUFJLENBQUMsTUFBTSxDQUFDLEtBQUssRUFBRSxHQUFHLEVBQUUsUUFBUSxDQUFDLENBQUE7UUFDakQsT0FBTyxJQUFJLENBQUMsYUFBYSxDQUFDLEdBQUcsQ0FBQyxHQUFHLEVBQUUsRUFBQyxPQUFPLEVBQUUsTUFBTSxFQUFFLFFBQVEsRUFBQyxDQUFDLENBQUE7SUFDbkUsQ0FBQztJQUVELFNBQVMsQ0FBQyxRQUFnQjtRQUN0QixNQUFNLEdBQUcsR0FBRyw4QkFBOEIsQ0FBQztRQUMzQyxNQUFNLE9BQU8sR0FBRyxJQUFJLENBQUMsTUFBTSxDQUFDLEtBQUssRUFBRSxHQUFHLEVBQUUsUUFBUSxDQUFDLENBQUE7UUFDakQsT0FBTyxJQUFJLENBQUMsYUFBYSxDQUFDLEdBQUcsQ0FBQyxHQUFHLEVBQUUsRUFBQyxPQUFPLEVBQUUsTUFBTSxFQUFFLFFBQVEsRUFBQyxDQUFDLENBQUE7SUFDbkUsQ0FBQztJQUVELE1BQU0sQ0FBQyxRQUFnQjtRQUNuQixNQUFNLEdBQUcsR0FBRywyQkFBMkIsQ0FBQztRQUN4QyxNQUFNLE9BQU8sR0FBRyxJQUFJLENBQUMsTUFBTSxDQUFDLEtBQUssRUFBRSxHQUFHLEVBQUUsUUFBUSxDQUFDLENBQUE7UUFDakQsT0FBTyxJQUFJLENBQUMsYUFBYSxDQUFDLEdBQUcsQ0FBQyxHQUFHLEVBQUUsRUFBQyxPQUFPLEVBQUMsQ0FBQyxDQUFBO0lBQ2pELENBQUM7SUFFRCxPQUFPLENBQUMsUUFBZ0I7UUFDcEIsTUFBTSxHQUFHLEdBQUcsNEJBQTRCLENBQUM7UUFDekMsTUFBTSxPQUFPLEdBQUcsSUFBSSxDQUFDLE1BQU0sQ0FBQyxLQUFLLEVBQUUsR0FBRyxFQUFFLFFBQVEsQ0FBQyxDQUFBO1FBQ2pELE9BQU8sSUFBSSxDQUFDLGFBQWEsQ0FBQyxHQUFHLENBQUMsR0FBRyxFQUFFLEVBQUMsT0FBTyxFQUFDLENBQUMsQ0FBQTtJQUNqRCxDQUFDO0lBRUQsS0FBSyxDQUFDLFFBQWdCO1FBQ2xCLE1BQU0sR0FBRyxHQUFHLDBCQUEwQixDQUFDO1FBQ3ZDLE1BQU0sT0FBTyxHQUFHLElBQUksQ0FBQyxNQUFNLENBQUMsS0FBSyxFQUFFLEdBQUcsRUFBRSxRQUFRLENBQUMsQ0FBQTtRQUNqRCxPQUFPLElBQUksQ0FBQyxhQUFhLENBQUMsR0FBRyxDQUFDLEdBQUcsRUFBRSxFQUFDLE9BQU8sRUFBRSxNQUFNLEVBQUUsUUFBUSxFQUFDLENBQUMsQ0FBQTtJQUNuRSxDQUFDO0lBRUQsT0FBTyxDQUFDLFFBQWdCO1FBQ3BCLE1BQU0sR0FBRyxHQUFHLDRCQUE0QixDQUFDO1FBQ3pDLE1BQU0sT0FBTyxHQUFHLElBQUksQ0FBQyxNQUFNLENBQUMsS0FBSyxFQUFFLEdBQUcsRUFBRSxRQUFRLENBQUMsQ0FBQTtRQUNqRCxPQUFPLElBQUksQ0FBQyxhQUFhLENBQUMsR0FBRyxDQUFDLEdBQUcsRUFBRSxFQUFDLE9BQU8sRUFBRSxNQUFNLEVBQUUsUUFBUSxFQUFDLENBQUMsQ0FBQTtJQUNuRSxDQUFDO0NBQ0o7QUFyQ0Qsb0NBcUNDIn0=