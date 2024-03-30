"use strict";
Object.defineProperty(exports, "__esModule", { value: true });
exports.SpotAccountApi = void 0;
const BaseApi_1 = require("../BaseApi");
class SpotAccountApi extends BaseApi_1.BaseApi {
    info() {
        const url = '/api/v2/spot/account/info';
        const headers = this.signer('GET', url, null);
        return this.axiosInstance.get(url, { headers });
    }
    assets(qsOrBody) {
        const url = '/api/v2/spot/account/assets';
        const headers = this.signer('GET', url, qsOrBody);
        return this.axiosInstance.get(url, { headers, params: qsOrBody });
    }
    bills(qsOrBody) {
        const url = '/api/v2/spot/account/bills';
        const headers = this.signer('POST', url, qsOrBody);
        return this.axiosInstance.post(url, qsOrBody, { headers });
    }
    transferRecords(qsOrBody) {
        const url = '/api/v2/spot/account/transferRecords';
        const headers = this.signer('GET', url, qsOrBody);
        return this.axiosInstance.get(url, { headers, params: qsOrBody });
    }
}
exports.SpotAccountApi = SpotAccountApi;
//# sourceMappingURL=data:application/json;base64,eyJ2ZXJzaW9uIjozLCJmaWxlIjoiU3BvdEFjY291bnRBcGkuanMiLCJzb3VyY2VSb290IjoiIiwic291cmNlcyI6WyIuLi8uLi8uLi9zcmMvbGliL3YyL1Nwb3RBY2NvdW50QXBpLnRzIl0sIm5hbWVzIjpbXSwibWFwcGluZ3MiOiI7OztBQUFBLHdDQUFtQztBQUVuQyxNQUFhLGNBQWUsU0FBUSxpQkFBTztJQUV2QyxJQUFJO1FBQ0EsTUFBTSxHQUFHLEdBQUcsMkJBQTJCLENBQUM7UUFDeEMsTUFBTSxPQUFPLEdBQUcsSUFBSSxDQUFDLE1BQU0sQ0FBQyxLQUFLLEVBQUUsR0FBRyxFQUFFLElBQUksQ0FBQyxDQUFBO1FBQzdDLE9BQU8sSUFBSSxDQUFDLGFBQWEsQ0FBQyxHQUFHLENBQUMsR0FBRyxFQUFFLEVBQUMsT0FBTyxFQUFDLENBQUMsQ0FBQTtJQUNqRCxDQUFDO0lBRUQsTUFBTSxDQUFDLFFBQWdCO1FBQ25CLE1BQU0sR0FBRyxHQUFHLDZCQUE2QixDQUFDO1FBQzFDLE1BQU0sT0FBTyxHQUFHLElBQUksQ0FBQyxNQUFNLENBQUMsS0FBSyxFQUFFLEdBQUcsRUFBRSxRQUFRLENBQUMsQ0FBQTtRQUNqRCxPQUFPLElBQUksQ0FBQyxhQUFhLENBQUMsR0FBRyxDQUFDLEdBQUcsRUFBRSxFQUFDLE9BQU8sRUFBRSxNQUFNLEVBQUUsUUFBUSxFQUFDLENBQUMsQ0FBQTtJQUNuRSxDQUFDO0lBRUQsS0FBSyxDQUFDLFFBQWdCO1FBQ2xCLE1BQU0sR0FBRyxHQUFHLDRCQUE0QixDQUFDO1FBQ3pDLE1BQU0sT0FBTyxHQUFHLElBQUksQ0FBQyxNQUFNLENBQUMsTUFBTSxFQUFFLEdBQUcsRUFBRSxRQUFRLENBQUMsQ0FBQTtRQUNsRCxPQUFPLElBQUksQ0FBQyxhQUFhLENBQUMsSUFBSSxDQUFDLEdBQUcsRUFBRSxRQUFRLEVBQUUsRUFBQyxPQUFPLEVBQUMsQ0FBQyxDQUFBO0lBQzVELENBQUM7SUFFRCxlQUFlLENBQUMsUUFBZ0I7UUFDNUIsTUFBTSxHQUFHLEdBQUcsc0NBQXNDLENBQUM7UUFDbkQsTUFBTSxPQUFPLEdBQUcsSUFBSSxDQUFDLE1BQU0sQ0FBQyxLQUFLLEVBQUUsR0FBRyxFQUFFLFFBQVEsQ0FBQyxDQUFBO1FBQ2pELE9BQU8sSUFBSSxDQUFDLGFBQWEsQ0FBQyxHQUFHLENBQUMsR0FBRyxFQUFFLEVBQUMsT0FBTyxFQUFFLE1BQU0sRUFBRSxRQUFRLEVBQUMsQ0FBQyxDQUFBO0lBQ25FLENBQUM7Q0FDSjtBQXpCRCx3Q0F5QkMifQ==