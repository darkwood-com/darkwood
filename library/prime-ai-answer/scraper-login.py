from playwright.sync_api import sync_playwright
import os
import time
import json

LINKEDIN_EMAIL = os.getenv("LINKEDIN_EMAIL")
LINKEDIN_PASSWORD = os.getenv("LINKEDIN_PASSWORD")
PROFILE_URL = os.getenv("PROFILE_URL")

def scrape_linkedin_posts(profile_url):
    with sync_playwright() as p:
        browser = p.chromium.launch(headless=True)  # Run without graphical interface
        page = browser.new_page()

        # Se connecter à LinkedIn
        page.goto("https://www.linkedin.com/login")
        page.fill("input[name='session_key']", LINKEDIN_EMAIL)
        page.fill("input[name='session_password']", LINKEDIN_PASSWORD)
        page.click("button[type='submit']")
        time.sleep(5)  # Wait for page to load

        # Aller sur le profil cible
        page.goto(profile_url)
        time.sleep(5)

        # Extraire les posts (ajuster le sélecteur si nécessaire)
        posts = page.query_selector_all("div.feed-shared-update-v2")
        extracted_posts = [post.inner_text() for post in posts]

        browser.close()
        return extracted_posts

if __name__ == "__main__":
    posts = scrape_linkedin_posts(PROFILE_URL)
    print(json.dumps(posts, indent=2, ensure_ascii=False))  # Display posts in JSON format