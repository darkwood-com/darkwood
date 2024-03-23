import "../app/globals.css";
import { useEffect, useState } from "react";
import axios from 'axios';

const apiUrl = 'https://127.0.0.1:8000'

export default function Token() {
  const [price, setPrice] = useState(0)

  useEffect(() => {
    const interval = setInterval(function() {
      axios.get(apiUrl + '/api/ticker')
        .then(function (response) {
          setPrice(response.data.data[0].lastPr)
        });
    }, 1000)

    return () => clearInterval(interval)
  }, []);

  useEffect(() => {
    console.log(price)
  }, [price]);
  
  return <div>Token price {price}</div>
}