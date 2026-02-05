# TaskTracker

A daily task tracking application built with **Laravel 12** and **Livewire 4 (Volt)**.

## Features

- **Calendar Navigation**: Browse tasks by day with an intuitive 7-day calendar strip and date picker
- **Daily Task Management**: Create, edit, complete, and delete tasks with priorities, categories, and tags
- **Filters**: Search, filter by status (all/completed/incomplete), category, and priority
- **Dashboard**: Overview with today's progress, weekly stats, streak counter, overdue alerts
- **Statistics & Heatmaps**: 90-day activity heatmap, completion trends, priority distribution, weekday performance, category breakdown, tag cloud
- **Authentication**: Full auth flow via Laravel Breeze (login, register, password reset, email verification)
- **Role-Based Access**: Admin and user roles with granular permissions via Spatie Laravel Permission

## Tech Stack

- Laravel 12 / PHP 8.4
- Livewire 4.1 (Volt functional components)
- Tailwind CSS 4 with Inter font
- SQLite (default, swappable)
- Laravel Breeze (Livewire scaffold)
- Spatie Laravel Permission 6.x

## Setup

```bash
composer install
npm install
cp .env.example .env
php artisan key:generate
touch database/database.sqlite
php artisan migrate --seed
npm run build
php artisan serve
```

## Test Accounts

| Email             | Password | Role  |
|-------------------|----------|-------|
| admin@example.com | password | admin |
| test@example.com  | password | user  |
| demo@example.com  | password | user  |

## Pages

| Route        | Description                                    |
|--------------|------------------------------------------------|
| `/`          | Landing page                                   |
| `/dashboard` | Overview with stats, overdue/upcoming tasks     |
| `/tasks`     | Daily task view with calendar bar and CRUD      |
| `/statistics`| Analytics: heatmap, trends, charts, tag cloud   |
| `/profile`   | User profile settings                          |

## Documentation

See [`.docs/IMPLEMENTATION.md`](.docs/IMPLEMENTATION.md) for architecture details and [`.docs/CHANGES.md`](.docs/CHANGES.md) for changelog.
