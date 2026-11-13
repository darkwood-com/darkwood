<p align="center">
  <a href="https://github.com/matyo91/prime-ai-answer">
    <img src="logo.png" width="auto" height="128px" alt="Prime AI Answer">
  </a>
</p>

**Prime AI Answer** is a fully **modular** and **AI-agnostic** automation system designed to **scrape social media posts** from various platforms and **generate intelligent responses**. The project supports **any AI model on the market** for natural language processing and uses **Uniflow** for workflow automation, making it highly flexible for different use cases.  

This system enables **automated engagement with social media posts**, applicable to **content automation, research, AI-driven interactions, and social media monitoring**.  

## **⚙️ Features**  
✔ **Multi-platform compatibility** – Works with LinkedIn, Twitter, Reddit, Facebook, and other networks  
✔ **AI-agnostic design** – Compatible with OpenAI, Claude, Gemini, LLaMA, Mistral, or any AI model  
✔ **Automation with Playwright** – Scrapes posts dynamically and processes them  
✔ **Workflow orchestration with [Uniflow](https://uniflow.io)** – Manages AI interactions, API requests, and logic execution  
✔ **Scalability** – Dockerized for easy deployment and scheduled execution  
✔ **Modular architecture** – Allows integration with external APIs or third-party services  

## **🛠️ Tech Stack**  
🔹 **Python** – Core language for web automation and processing  
🔹 **Playwright** – Scraping and automation of social media platforms  
🔹 **[Uniflow](https://uniflow.io)** – Workflow automation and process management  
🔹 **Any AI Model** – Compatible with OpenAI GPT-4, Claude, Gemini, LLaMA, Mistral, Grok, or custom models  
🔹 **Docker & Docker Compose** – Containerized environment for easy deployment  

## **🚀 Installation & Setup**  
### **1️⃣ Clone the repository**  
```sh
git clone https://github.com/yourusername/prime-ai-answer.git
cd prime-ai-answer
```

### **2️⃣ Build and start the service**  
```sh
docker-compose build
docker-compose up -d
```

### **3️⃣ Access the running container (for testing)**  
```sh
docker exec -it prime-ai-answer /bin/sh
```

### **4️⃣ Manually execute the scraper (if needed)**  
```sh
python /app/scraper.py
```

## **📝 Usage**  
1️⃣ **Scraping social media posts**  
   - The system fetches posts from a specified user profile.  
   - Custom selectors can be adjusted for different platforms.  

2️⃣ **Generating AI-powered responses**  
   - Extracted content is sent to **any AI model** via Uniflow for contextual response generation.  

3️⃣ **Automating responses**  
   - The AI-generated replies can be posted back to the platform (if configured).  

4️⃣ **Scheduling execution**  
   - The bot runs automatically via **cron inside Docker**, executing at predefined intervals.  

## **⚠️ Disclaimer**  
This project is intended for **educational and research purposes only**.  
Unauthorized scraping and automation of social media interactions may violate platform policies.  
**Use responsibly and ensure compliance with platform terms of service.**  