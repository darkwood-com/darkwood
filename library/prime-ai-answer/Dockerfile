FROM mcr.microsoft.com/playwright/python:v1.35.0-focal

WORKDIR /app

COPY requirements.txt .
RUN pip install --no-cache-dir -r requirements.txt

COPY . .

# Install Playwright browsers
RUN playwright install chromium

EXPOSE 3000

CMD ["python", "scraper.py"]
