ZapLink - URL Shortener
A modern, production-ready URL shortening service built with Laravel. Transform long, unwieldy URLs into clean, memorable short links with real-time analytics.

🚀 Why I Built This
URL shorteners are everywhere, but most of them are either bloated with ads, track you relentlessly, or charge you for basic features. I wanted something different - a clean, fast, and transparent URL shortener that actually respects its users. ZapLink is what I came up with.

✨ Features
Instant URL Shortening - Paste a long URL, get a short link in milliseconds

Custom Short Codes - Personalize your links with memorable names

Real-time Analytics - Track clicks, unique visitors, and referrer sources

Link Expiration - Set automatic expiry dates for temporary links

User Dashboard - Manage all your shortened links in one place

Rate Limiting - Protection against abuse and spam

QR Code Generation - Generate QR codes for your short links (coming soon)

RESTful API - Programmatically create and manage short links

Responsive Design - Works on desktop, tablet, and mobile

🛠️ Tech Stack
Backend: Laravel 13.x

Frontend: Blade Templates + Tailwind CSS

Database: SQLite (Development) / MySQL (Production)

Cache: File Cache (Development) / Redis (Production)

Queue: Database (Development) / Redis (Production)



📋 Prerequisites
Before you begin, ensure you have the following installed:

PHP >= 8.2

Composer

Node.js & NPM

SQLite or MySQL

🔧 Installation
1. Clone the Repository
bash
git clone https://github.com/patrickiyiakimo/ZapLink.git
cd zap-link

2. Install PHP Dependencies
bash
composer install

3. Install NPM Dependencies
bash
npm install
npm run build

4. Environment Configuration
bash
cp .env.example .env
php artisan key:generate
Update your .env file with your database credentials:

📁 Project Structure
Here's a breakdown of the key parts of the codebase:

Core Directories
text
app/
├── Http/
│   ├── Controllers/
│   │   ├── Auth/        # Authentication controllers
│   │   ├── Web/         # Web interface controllers
│   │   └── Api/         # RESTful API controllers
│   ├── Middleware/      # Custom middleware (Rate limiting, URL validation)
│   └── Requests/        # Form requests with validation rules
├── Models/              # Eloquent models (Link, Visit, User)
├── Services/            # Business logic layer
│   ├── LinkService.php      # Core link operations
│   ├── AnalyticsService.php # Analytics & statistics
│   └── UrlValidatorService.php # URL validation & security
└── Repositories/        # Data access layer (optional, but I like the separation)
Key Design Decisions
Service Layer Pattern
I put all the business logic in service classes rather than bloating the controllers. This keeps the controllers thin and makes testing easier. The LinkService handles all the link operations - creation, resolution, and validation.

Custom Validation
The UrlValidatorService checks URLs for safety before shortening. It prevents:

Malicious or spam domains

Self-referencing loops

Already shortened URLs

Repository Pattern
I used repositories for database interactions. This might be overkill for a small project, but it makes the code more testable and keeps the data access logic separate from business logic.

Why These Patterns?
After 3 years of writing PHP, I've learned that the biggest challenge isn't writing code that works - it's writing code that can be maintained 6 months later. These patterns:

Keep things organized - You always know where to find what

Make testing easier - You can mock repositories and services

Prevent code duplication - Common logic lives in one place

Scale better - Adding new features doesn't break existing ones

🚧 What I'd Add Next
If I had more time, I'd build:

OAuth Authentication - Social login options

Team Collaboration - Share links with team members

Advanced Analytics - Geographic data, device breakdown

Bulk URL Shortening - Upload CSV files

Webhook Integration - Notifications on link clicks

Custom Domains - Use your own domain for short links

Browser Extensions - Chrome/Firefox extension for quick shortening