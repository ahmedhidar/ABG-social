# ABG Social Media Platform

A modern, high-performance social media application built with Laravel 11, featuring real-time interactions, a robust API, and a sleek frontend powered by Alpine.js and Tailwind CSS.

## 🚀 Features

- **Authentication System**: Secure web and API authentication using Laravel Sanctum.
- **Dynamic News Feed**: Efficiently fetches posts from the user and their friends with optimized Eloquent queries.
- **Real-time Notifications**: Instant alerts for likes, comments, and friend requests powered by Pusher and Laravel Echo.
- **Friendship System**: Complete workflow including sending, accepting, rejecting, and cancelling requests with instant AJAX UI updates.
- **Media Management**: Post creation with image uploads using UUID-based file naming for improved security and collision avoidance.
- **Social Interactions**: Robust like and comment system across both news feed and profile pages.
- **Profile Customization**: User profiles with bios, avatars, and a dedicated personal post history.
- **API Documentation**: Comprehensive OpenAPI/Swagger documentation accessible at `/api/documentation`.

## 🛠️ Tech Stack

- **Backend**: Laravel 11 (PHP 8.2+)
- **Frontend**: Alpine.js, Axios, Blade, Tailwind CSS
- **Real-time**: Pusher + Laravel Echo
- **API**: Sanctum (Auth), L5-Swagger (Docs)
- **Database**: MySQL/PostgreSQL (supports SQLite for testing)

## 📦 Installation

1. **Clone the repository**:
   ```bash
   git clone https://github.com/ahmedhidar/ABG-social.git
   cd ABG-social
   ```

2. **Install dependencies**:
   ```bash
   composer install
   npm install
   ```

3. **Environment Setup**:
   ```bash
   cp .env.example .env
   php artisan key:generate
   ```
   *Configure your Database and Pusher credentials in `.env`.*

4. **Run Migrations & Link Storage**:
   ```bash
   php artisan migrate
   php artisan storage:link
   ```

5. **Generate API Documentation**:
   ```bash
   php artisan l5-swagger:generate
   ```

## 🖥️ Running Locally

Start the development servers:

```bash
# Terminal 1: Laravel Server
php artisan serve

# Terminal 2: Asset Bundling
npm run dev

# Terminal 3: Queue Worker (for notifications)
php artisan queue:work
```


---
Developed with ❤️ by Ahmed Hidar .
