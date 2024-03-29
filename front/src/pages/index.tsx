import "../app/globals.css";
import { useEffect, useState } from "react";
import axios from 'axios';

//const apiUrl = 'https://192.168.1.11:8000'
const apiUrl = 'https://127.0.0.1:8000'
const timeout = 1000
const maxSize = 300
const taker = 0.06
const maker = 0.02
let symbolDatas: any = {}
const adverage = (values: any, limit: any) => {
  let sum = 0
  for(let i = 0; i < limit; i++) {
    sum += values[i] ?? 0
  }
  return sum / limit
}

export default function Token() {
  const [symbols, setSymbols] = useState([])
  const [currentSymbol, setCurrentSymbol] = useState('BTCUSDT')
  const [price, setPrice] = useState(0)
  const [account, setAccount] = useState(0)
  const [positions, setPositions] = useState([])

  useEffect(() => {
    let symbols = []
    let price = 0
    let account = 0
    let positions = []

    const interval = setInterval(async function() {
      axios.get(apiUrl + '/api/tickers')
        .then(function (response) {
          symbols = response.data.data

          for(let i = 0; i < symbols.length; i++) {
            let symbol = symbols[i].symbol
            if(!symbolDatas[symbol]) {
              symbolDatas[symbol] = {
                values: [],
              }
            }
            let lastPr = parseFloat(symbols[i].lastPr)
            symbolDatas[symbol].value = lastPr
            symbolDatas[symbol].values.unshift(lastPr)
            symbolDatas[symbol].values = symbolDatas[symbol].values.slice(0, maxSize)
            symbols[i].adverage = []
            symbols[i].variations = []
            symbols[i].acceleration = []
            for(let j = 5; j < maxSize; j += 5) {
              let adv = adverage(symbolDatas[symbol].values, j)
              symbols[i].adverage.push(adv)
              symbols[i].variations.push((adv - lastPr) / lastPr)
            }
            for(let j = 0; j < symbols[i].variations.length - 1; j++) {
              symbols[i].acceleration.push((symbols[i].variations[j+1] - symbols[i].variations[j]) / symbols[i].variations[j+1])
            }
          }
          /*symbols.sort((symbol1: any, symbol2: any) => {
            return Math.abs(symbol2.variations[0]) - Math.abs(symbol1.variations[0])
            //return Math.abs(symbol2.acceleration[0]) - Math.abs(symbol1.acceleration[0])
          })*/
          setSymbols(symbols)
        });
      axios.post(apiUrl + '/api/ticker', {symbol: currentSymbol})
        .then(function (response) {
          price = response.data.data[0].lastPr
          setPrice(price)
        });
      axios.get(apiUrl + '/api/account')
        .then(function (response) {
          account = response.data.data[0].available
          setAccount(account)
        });
      axios.get(apiUrl + '/api/positions')
        .then(function (response) {
          if(Object.entries(symbolDatas).length === 0) {
            return;
          }

          positions = response.data.data
          for(let i = 0; i < positions.length; i++) {
            let available = parseFloat(positions[i].available)
            let open = parseFloat(positions[i].openPriceAvg)
            let value = parseFloat(symbolDatas[positions[i].symbol].value)
            let holdSide = positions[i].holdSide

            positions[i].feeTaker = available * open * taker / 100
            positions[i].feeMaker = available * value * taker / 100
            if(holdSide === 'short') {
              positions[i].profit = available * (open - value)
            } else {
              positions[i].profit = available * (value - open)
            }
            positions[i].total = positions[i].profit - positions[i].feeTaker - positions[i].feeMaker
          }

          setPositions(positions)
        });
    }, timeout)

    return () => clearInterval(interval)
  }, [currentSymbol]);
  
  return <div>
    <ul>
      <li>Current token {currentSymbol}</li>
      <li>Token price {price}</li>
      <li>Account {account}</li>
      {positions.map((position: any, i) => (
        <li key={i}><a href={"https://www.bitget.com/fr/futures/usdt/" + position.symbol} target="_blank">Position {position.symbol}</a> {position.openPriceAvg} | feeTaker {position.feeTaker} | feeMaker {position.feeMaker} | profits {position.profit} | total {position.total}</li>
      ))}
    </ul>

    <table>
      <tbody>
    {symbols.map((symbol: any, i) => {
      return <tr key={i}>
            <td><a href={"https://www.bitget.com/fr/futures/usdt/" + symbol.symbol} target="_blank">{symbol.symbol}</a></td>
            <td>{symbol.lastPr}</td>
            {symbol.variations.map((variation: any, j: any) => (
            <td key={j}>{variation}</td>
            ))}
        </tr>
      })}
      </tbody>
    </table>

    <form className="max-w-sm mx-auto">
      <select
        id="symbols"
        className="bg-gray-50 border border-gray-300 text-gray-900 text-sm rounded-lg focus:ring-blue-500 focus:border-blue-500 block w-full p-2.5 dark:bg-gray-700 dark:border-gray-600 dark:placeholder-gray-400 dark:text-white dark:focus:ring-blue-500 dark:focus:border-blue-500"
        onChange={(event) => setCurrentSymbol(event.target.value)}
        >
        <option defaultValue="">Choose a token</option>
        {symbols.map((symbol: any, i) => (
        <option value={symbol.symbol} key={symbol.symbol}>{symbol.symbol}</option>
        ))}
      </select>
      <button className="bg-blue-500 hover:bg-blue-700 text-white font-bold py-2 px-4 rounded">
          Open order
      </button>
      <div className="mb-5">
        <label className="block mb-2 text-sm font-medium text-gray-900 dark:text-white">Your email</label>
        <input type="text" className="bg-gray-50 border border-gray-300 text-gray-900 text-sm rounded-lg focus:ring-blue-500 focus:border-blue-500 block w-full p-2.5 dark:bg-gray-700 dark:border-gray-600 dark:placeholder-gray-400 dark:text-white dark:focus:ring-blue-500 dark:focus:border-blue-500" placeholder="name@flowbite.com" required />
      </div>
      <button type="submit" className="text-white bg-blue-700 hover:bg-blue-800 focus:ring-4 focus:outline-none focus:ring-blue-300 font-medium rounded-lg text-sm w-full sm:w-auto px-5 py-2.5 text-center dark:bg-blue-600 dark:hover:bg-blue-700 dark:focus:ring-blue-800">Submit</button>
    </form>
  </div>
}