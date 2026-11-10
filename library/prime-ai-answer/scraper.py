from playwright.sync_api import sync_playwright
import os
import time
import json
from flask import Flask, jsonify, request
from flask_cors import CORS

app = Flask(__name__)
CORS(app)

def scrape_linkedin_posts(profile_url, session_cookie):
    with sync_playwright() as p:
        browser = p.chromium.launch(headless=True)
        context = browser.new_context()

        # Set LinkedIn session cookie with additional required cookies
        context.add_cookies([
            {
                "name": "li_at",
                "value": session_cookie,
                "domain": ".linkedin.com",
                "path": "/"
            },
            {
                "name": "JSESSIONID",
                "value": f"ajax:{session_cookie.split(':')[0]}",
                "domain": ".linkedin.com",
                "path": "/"
            }
        ])
        
        page = context.new_page()
        
        # Add error checking for navigation
        try:
            response = page.goto(profile_url, wait_until="networkidle")
            if not response.ok:
                raise Exception(f"Failed to load page: {response.status}")
            
            # Check if we're actually logged in
            if "login" in page.url:
                raise Exception("Not properly authenticated - check your LinkedIn session cookie")
                
            page.wait_for_timeout(5000)  # Let it load

            # Scrape posts
            posts = page.query_selector_all("div.feed-shared-update-v2")
            if not posts:
                raise Exception("No posts found - might be an authentication issue")
                
            extracted_posts = [post.inner_text() for post in posts]

        except Exception as e:
            browser.close()
            raise Exception(f"Scraping failed: {str(e)}")

        browser.close()
        return extracted_posts

@app.route('/scrape_linkedin', methods=['POST'])
def scrape():
    try:
        data = request.get_json()
        if not data or 'profile_url' not in data:
            return jsonify({"success": False, "error": "Profile URL is required"}), 400
        if 'session_cookie' not in data:
            return jsonify({"success": False, "error": "LinkedIn session cookie is required"}), 400
            
        profile_url = data['profile_url']
        session_cookie = data['session_cookie']
        posts = scrape_linkedin_posts(profile_url, session_cookie)
        return jsonify({"success": True, "posts": posts})
    except Exception as e:
        return jsonify({"success": False, "error": str(e)}), 500

if __name__ == "__main__":
    app.run(host='0.0.0.0', port=3000)
