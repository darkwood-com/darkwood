"use strict";
Object.defineProperty(exports, "__esModule", { value: true });
exports.sortByKey = exports.encryptRSA = exports.toJsonString = exports.encrypt = void 0;
const querystring_1 = require("querystring");
const crypto_1 = require("crypto");
const config_1 = require("./config");
const contant_1 = require("./contant");
/**
 * 获取签名器
 * @param apiKey
 * @param secretKey
 * @param timestamp
 * @param passphrase
 */
function getSigner(apiKey = '', secretKey = '', passphrase = '') {
    return (httpMethod, url, qsOrBody, locale = 'zh-CN') => {
        const timestamp = Date.now();
        let signString = encrypt(httpMethod, url, qsOrBody, timestamp, secretKey);
        if (config_1.API_CONFIG.SIGN_TYPE === contant_1.BIZ_CONSTANT.RSA) {
            signString = encryptRSA(httpMethod, url, qsOrBody, timestamp, secretKey);
        }
        return {
            'ACCESS-SIGN': signString,
            'ACCESS-TIMESTAMP': timestamp,
            'ACCESS-KEY': apiKey,
            'ACCESS-PASSPHRASE': passphrase,
            'Content-Type': 'application/json',
            Cookie: 'locale=' + locale,
            locale
        };
    };
}
exports.default = getSigner;
/**
 * 加密算法
 * @param httpMethod
 * @param url
 * @param qsOrBody
 * @param timestamp
 * @param secretKey
 */
function encrypt(httpMethod, url, qsOrBody, timestamp, secretKey) {
    httpMethod = httpMethod.toUpperCase();
    if (qsOrBody && httpMethod === 'GET') {
        qsOrBody = sortByKey(qsOrBody);
    }
    if (qsOrBody && Object.keys(qsOrBody).length === 0) {
        qsOrBody = null;
    }
    // const qsOrBodyStr = qsOrBody ? httpMethod === 'GET' ? '?' + stringify(qsOrBody) : toJsonString(qsOrBody) : ''
    const qsOrBodyStr = qsOrBody ? httpMethod === 'GET' ? '?' + unescapedStringify(qsOrBody) : toJsonString(qsOrBody) : '';
    const preHash = String(timestamp) + httpMethod + url + qsOrBodyStr;
    const mac = crypto_1.createHmac('sha256', secretKey);
    const preHashToMacBuffer = mac.update(preHash).digest();
    return preHashToMacBuffer.toString('base64');
}
exports.encrypt = encrypt;
function unescapedStringify(formDataDict) {
    const encodedData = Object.keys(formDataDict).map((eachKey) => {
        return eachKey + '=' + formDataDict[eachKey];
    }).join('&');
    return encodedData;
}
function toJsonString(obj) {
    if (obj == null) {
        return null;
    }
    let json = JSON.stringify(obj);
    Object.keys(obj).filter(key => key[0] === '_').forEach(key => {
        json = json.replace(key, key.substring(1));
    });
    const reg = new RegExp('"_', 'g');
    return json.replace(reg, '"');
}
exports.toJsonString = toJsonString;
/**
 * RSA加密算法
 * @param httpMethod
 * @param url
 * @param qsOrBody
 * @param timestamp
 * @param secretKey
 */
function encryptRSA(httpMethod, url, qsOrBody, timestamp, secretKey) {
    httpMethod = httpMethod.toUpperCase();
    const qsOrBodyStr = qsOrBody ? httpMethod === 'GET' ? '?' + querystring_1.stringify(qsOrBody) : toJsonString(qsOrBody) : '';
    const preHash = String(timestamp) + httpMethod + url + qsOrBodyStr;
    const NodeRSA = require('node-rsa');
    const priKey = new NodeRSA(secretKey);
    const sign = priKey.sign(preHash, 'base64', 'UTF-8');
    return sign;
}
exports.encryptRSA = encryptRSA;
function sortByKey(dict) {
    const sorted = [];
    for (const key of Object.keys(dict)) {
        sorted[sorted.length] = key;
    }
    sorted.sort();
    const tempDict = {};
    // for(let i = 0; i < sorted.length; i++) {
    //     tempDict[sorted[i]] = dict[sorted[i]];
    // }
    for (const item of sorted) {
        tempDict[item] = dict[item];
    }
    return tempDict;
}
exports.sortByKey = sortByKey;
//# sourceMappingURL=data:application/json;base64,eyJ2ZXJzaW9uIjozLCJmaWxlIjoidXRpbC5qcyIsInNvdXJjZVJvb3QiOiIiLCJzb3VyY2VzIjpbIi4uLy4uL3NyYy9saWIvdXRpbC50cyJdLCJuYW1lcyI6W10sIm1hcHBpbmdzIjoiOzs7QUFBQSw2Q0FBcUM7QUFDckMsbUNBQWlDO0FBRWpDLHFDQUFvQztBQUNwQyx1Q0FBdUM7QUFZdkM7Ozs7OztHQU1HO0FBQ0gsU0FBd0IsU0FBUyxDQUM3QixTQUFpQixFQUFFLEVBQ25CLFlBQW9CLEVBQUUsRUFDdEIsYUFBcUIsRUFBRTtJQUd2QixPQUFPLENBQUMsVUFBa0IsRUFBRSxHQUFXLEVBQUUsUUFBaUMsRUFBRSxNQUFNLEdBQUcsT0FBTyxFQUFFLEVBQUU7UUFDNUYsTUFBTSxTQUFTLEdBQUcsSUFBSSxDQUFDLEdBQUcsRUFBRSxDQUFDO1FBQzdCLElBQUksVUFBVSxHQUFHLE9BQU8sQ0FBQyxVQUFVLEVBQUUsR0FBRyxFQUFFLFFBQVEsRUFBRSxTQUFTLEVBQUUsU0FBUyxDQUFDLENBQUM7UUFDMUUsSUFBSSxtQkFBVSxDQUFDLFNBQVMsS0FBSyxzQkFBWSxDQUFDLEdBQUcsRUFBRTtZQUMzQyxVQUFVLEdBQUcsVUFBVSxDQUFDLFVBQVUsRUFBRSxHQUFHLEVBQUUsUUFBUSxFQUFFLFNBQVMsRUFBQyxTQUFTLENBQUMsQ0FBQTtTQUMxRTtRQUVELE9BQU87WUFDSCxhQUFhLEVBQUUsVUFBVTtZQUN6QixrQkFBa0IsRUFBRSxTQUFTO1lBQzdCLFlBQVksRUFBRSxNQUFNO1lBQ3BCLG1CQUFtQixFQUFFLFVBQVU7WUFDL0IsY0FBYyxFQUFFLGtCQUFrQjtZQUNsQyxNQUFNLEVBQUUsU0FBUyxHQUFHLE1BQU07WUFDMUIsTUFBTTtTQUNULENBQUE7SUFDTCxDQUFDLENBQUE7QUFDTCxDQUFDO0FBdkJELDRCQXVCQztBQUVEOzs7Ozs7O0dBT0c7QUFDSCxTQUFnQixPQUFPLENBQUMsVUFBa0IsRUFBRSxHQUFXLEVBQUUsUUFBNkMsRUFBRSxTQUFpQixFQUFDLFNBQWdCO0lBQ3RJLFVBQVUsR0FBRyxV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