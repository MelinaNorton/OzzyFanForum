# Ozzy Fan Forum

A community-driven discussion board for Ozzy Osbourne fans, built with PHP, JavaScript (jQuery & AJAX) and the Spotify Web API. Users can create and browse topics, post replies, attach media links, and report inappropriate content. Admins have a dedicated console for reviewing flagged posts.

---
## Tech Stack & Tools

- **Backend:** PHP 7.x+, MySQL  
- **Frontend:** HTML5, CSS3, JavaScript, jQuery, AJAX  
- **API:** Spotify Web API (embed track links)  
- **Web server:** Apache / Nginx  
- **Dependencies / Dev Tools:**  
  - [Composer](https://getcomposer.org/) (PHP library management)  
  - [phpMyAdmin](https://www.phpmyadmin.net/) (DB administration)  
  - Git (version control)  

---

##  Installation

1. Clone the repo  
   ```bash
   git clone https://github.com/your-org/ozzy-fan-forum.git
   cd ozzy-fan-forum

## Features
User Authentication:
  - Sign up / log in with secure password hashing
  - Role-based access (regular user vs. admin)

Topics & Posts:
  - Create topics with title, tags, and optional Spotify links
  - View topic list (all topics or “My Topics” for your own)
  - Post replies under any topic
  - Delete your own posts at any time
    
Media Embedding:
  - Paste a Spotify URL when creating/editing topics to display a play icon that opens the track in a new tab

Reporting & Moderation:
  - Report posts via a button

Admin console lists all flagged posts for review


