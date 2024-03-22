import "../app/globals.css";
import { useEffect } from "react";
import axios from 'axios';
import Image from "next/image";

export default function Token() {
  useEffect(() => {
    axios.get('https://127.0.0.1:8000/api/trade')
      .then(function (response) {
        console.log(response.data);
      })
      .catch(function (error) {
        console.log(error);
      });
  }, []);
  
  return <main className="flex min-h-screen flex-col items-center justify-between p-24">
    <figure className="bg-slate-100 rounded-xl p-8 dark:bg-slate-800">
      <Image className="w-24 h-24 rounded-full mx-au" src="/vercel.svg" alt="" width="384" height="512" />
      <div className="pt-6 space-y-4">
        <blockquote>
          <p className="text-lg">
            “Tailwind CSS is the only framework that I've seen scale
            on large teams. It s easy to customize, adapts to any design,
            and the build size is tiny.”
          </p>
        </blockquote>
        <figcaption>
          <div>
            Sarah Dayan
          </div>
          <div>
            Staff Engineer, Algolia
          </div>
        </figcaption>
      </div>
    </figure>
  </main>
}