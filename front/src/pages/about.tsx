import { useEffect } from "react";
import axios from 'axios';

export default function About() {
  useEffect(() => {
    axios.get('https://127.0.0.1:8000/api/trade')
      .then(function (response) {
        console.log(response.data);
      })
      .catch(function (error) {
        console.log(error);
      });
  }, []);
  
  return <div>About</div>
}