# laravel-task-tracker
Interactive task tracker into a full-featured Laravel 12 + Livewire 3 application with dynamic content, authentication, and enhanced functionality for diverse use cases.


# Task Tracker - Repository Setup & Installation Guide

## 🚀 Quick Start

### Prerequisites
- PHP 8.2 or higher
- Composer 2.0+
- Node.js 18+ & NPM
- MySQL 8.0+ or PostgreSQL 13+
- Redis (recommended)

### One-Command Setup
```bash
git clone https://github.com/CodeWith-PeterBull/laravel-task-tracker.git
cd laravel-task-tracker
./setup.sh
```

## 📦 Manual Installation

### Step 1: Create Laravel Project
```bash
# Create new Laravel 12 project
composer create-project laravel/laravel task-tracker "^12.0"
cd task-tracker

# Set proper permissions
chmod -R 755 storage bootstrap/cache
```

### Step 2: Install Dependencies
```bash
# Core dependencies
composer require livewire/livewire "^3.0"
composer require laravel/breeze "^2.0"

# Additional packages
composer require spatie/laravel-permission "^6.0"
composer require intervention/image "^3.0"
composer require maatwebsite/excel "^3.1"
composer require pusher/pusher-php-server "^7.0"
composer require spatie/laravel-backup "^8.0"

# Development dependencies
composer require --dev laravel/pint
composer require --dev pestphp/pest
composer require --dev pestphp/pest-plugin-laravel
```

### Step 3: Frontend Setup
```bash
# Install Node dependencies
npm install

# Additional frontend packages
npm install @tailwindcss/forms
npm install @tailwindcss/typography
npm install alpinejs
npm install chart.js
npm install flatpickr
npm install sortablejs
```

### Step 4: Environment Configuration
```bash
# Copy environment file
cp .env.example .env

# Generate application key
php artisan key:generate
```

### Environment Variables (.env)
```env
APP_NAME="Task Tracker"
APP_ENV=local
APP_KEY=base64:generated_key_here
APP_DEBUG=true
APP_URL=http://localhost:8000

LOG_CHANNEL=stack
LOG_DEPRECATIONS_CHANNEL=null
LOG_LEVEL=debug

DB_CONNECTION=mysql
DB_HOST=127.0.0.1
DB_PORT=3306
DB_DATABASE=task_tracker
DB_USERNAME=root
DB_PASSWORD=

BROADCAST_DRIVER=pusher
CACHE_DRIVER=redis
FILESYSTEM_DISK=local
QUEUE_CONNECTION=redis
SESSION_DRIVER=redis
SESSION_LIFETIME=120

MEMCACHED_HOST=127.0.0.1

REDIS_HOST=127.0.0.1
REDIS_PASSWORD=null
REDIS_PORT=6379

MAIL_MAILER=smtp
MAIL_HOST=mailpit
MAIL_PORT=1025
MAIL_USERNAME=null
MAIL_PASSWORD=null
MAIL_ENCRYPTION=null
MAIL_FROM_ADDRESS="hello@example.com"
MAIL_FROM_NAME="${APP_NAME}"

AWS_ACCESS_KEY_ID=
AWS_SECRET_ACCESS_KEY=
AWS_DEFAULT_REGION=us-east-1
AWS_BUCKET=
AWS_USE_PATH_STYLE_ENDPOINT=false

PUSHER_APP_ID=
PUSHER_APP_KEY=
PUSHER_APP_SECRET=
PUSHER_HOST=
PUSHER_PORT=443
PUSHER_SCHEME=https
PUSHER_APP_CLUSTER=mt1

VITE_APP_NAME="${APP_NAME}"
VITE_PUSHER_APP_KEY="${PUSHER_APP_KEY}"
VITE_PUSHER_HOST="${PUSHER_HOST}"
VITE_PUSHER_PORT="${PUSHER_PORT}"
VITE_PUSHER_SCHEME="${PUSHER_SCHEME}"
VITE_PUSHER_APP_CLUSTER="${PUSHER_APP_CLUSTER}"
```

### Step 5: Database Setup
```bash
# Create database
mysql -u root -p -e "CREATE DATABASE task_tracker;"

# Run migrations
php artisan migrate

# Seed database with sample data
php artisan db:seed
```

### Step 6: Authentication Setup
```bash
# Install Laravel Breeze
php artisan breeze:install

# Choose: Blade with Alpine
# When prompted, select "yes" for dark mode support

# Publish Livewire assets
php artisan livewire:publish --assets
```

### Step 7: Asset Compilation
```bash
# Development
npm run dev

# Production
npm run build
```

### Step 8: Queue & Cache Setup
```bash
# Clear caches
php artisan config:cache
php artisan route:cache
php artisan view:cache

# Start queue worker (in separate terminal)
php artisan queue:work

# Start development server
php artisan serve
```

## 🗂️ Project Structure

```
task-tracker/
├── app/
│   ├── Console/
│   │   └── Commands/
│   ├── Events/
│   │   ├── TaskCompleted.php
│   │   ├── TrackerCreated.php
│   │   └── ProgressMilestoneReached.php
│   ├── Http/
│   │   ├── Controllers/
│   │   │   ├── DashboardController.php
│   │   │   └── ExportController.php
│   │   ├── Middleware/
│   │   │   └── EnsureTrackerOwnership.php
│   │   └── Requests/
│   │       ├── StoreTrackerRequest.php
│   │       └── StoreTaskRequest.php
│   ├── Livewire/
│   │   ├── Tracker/
│   │   │   ├── TrackerDashboard.php
│   │   │   ├── TrackerCreate.php
│   │   │   ├── TrackerEdit.php
│   │   │   └── TrackerShow.php
│   │   ├── Task/
│   │   │   ├── TaskCreate.php
│   │   │   ├── TaskEdit.php
│   │   │   ├── TaskList.php
│   │   │   └── TaskItem.php
│   │   └── Components/
│   │       ├── ProgressBar.php
│   │       ├── TagManager.php
│   │       └── NotificationCenter.php
│   ├── Models/
│   │   ├── User.php
│   │   ├── Tracker.php
│   │   ├── Task.php
│   │   ├── Tag.php
│   │   ├── TaskCompletion.php
│   │   └── TrackerTemplate.php
│   ├── Repositories/
│   │   ├── Contracts/
│   │   │   ├── TrackerRepositoryInterface.php
│   │   │   ├── TaskRepositoryInterface.php
│   │   │   └── TagRepositoryInterface.php
│   │   ├── TrackerRepository.php
│   │   ├── TaskRepository.php
│   │   └── TagRepository.php
│   ├── Services/
│   │   ├── TrackerService.php
│   │   ├── TaskService.php
│   │   ├── NotificationService.php
│   │   ├── ProgressCalculatorService.php
│   │   └── ExportService.php
│   └── Policies/
│       ├── TrackerPolicy.php
│       └── TaskPolicy.php
├── database/
│   ├── factories/
│   │   ├── TrackerFactory.php
│   │   ├── TaskFactory.php
│   │   └── TagFactory.php
│   ├── migrations/
│   │   ├── 2024_01_01_000001_create_trackers_table.php
│   │   ├── 2024_01_01_000002_create_tasks_table.php
│   │   ├── 2024_01_01_000003_create_tags_table.php
│   │   ├── 2024_01_01_000004_create_task_tag_table.php
│   │   ├── 2024_01_01_000005_create_task_completions_table.php
│   │   └── 2024_01_01_000006_create_tracker_templates_table.php
│   └── seeders/
│       ├── DatabaseSeeder.php
│       ├── TrackerTemplateSeeder.php
│       └── DemoDataSeeder.php
├── resources/
│   ├── css/
│   │   └── app.css
│   ├── js/
│   │   ├── app.js
│   │   └── components/
│   │       ├── progress-bar.js
│   │       ├── task-sorter.js
│   │       └── notification.js
│   └── views/
│       ├── layouts/
│       │   ├── app.blade.php
│       │   └── guest.blade.php
│       ├── livewire/
│       │   ├── tracker/
│       │   │   ├── dashboard.blade.php
│       │   │   ├── create.blade.php
│       │   │   ├── edit.blade.php
│       │   │   └── show.blade.php
│       │   ├── task/
│       │   │   ├── create.blade.php
│       │   │   ├── edit.blade.php
│       │   │   ├── list.blade.php
│       │   │   └── item.blade.php
│       │   └── components/
│       │       ├── progress-bar.blade.php
│       │       ├── tag-manager.blade.php
│       │       └── notification-center.blade.php
│       ├── dashboard.blade.php
│       └── welcome.blade.php
├── routes/
│   ├── web.php
│   ├── api.php
│   └── console.php
├── tests/
│   ├── Feature/
│   │   ├── TrackerTest.php
│   │   ├── TaskTest.php
│   │   └── AuthTest.php
│   └── Unit/
│       ├── TrackerServiceTest.php
│       └── TaskServiceTest.php
├── config/
├── storage/
├── public/
├── .env.example
├── composer.json
├── package.json
├── tailwind.config.js
├── vite.config.js
├── setup.sh
└── README.md
```

## 🛠️ Setup Script (setup.sh)

```bash
#!/bin/bash

echo "🚀 Setting up Laravel Task Tracker..."

# Check if composer is installed
if ! command -v composer &> /dev/null; then
    echo "❌ Composer is not installed. Please install it first."
    exit 1
fi

# Check if node is installed
if ! command -v node &> /dev/null; then
    echo "❌ Node.js is not installed. Please install it first."
    exit 1
fi

# Install PHP dependencies
echo "📦 Installing PHP dependencies..."
composer install

# Copy environment file
if [ ! -f .env ]; then
    echo "📄 Creating environment file..."
    cp .env.example .env
fi

# Generate application key
echo "🔑 Generating application key..."
php artisan key:generate

# Install Node dependencies
echo "📦 Installing Node.js dependencies..."
npm install

# Create database (if using SQLite for simplicity)
echo "🗄️ Setting up database..."
touch database/database.sqlite

# Update .env for SQLite
sed -i 's/DB_CONNECTION=mysql/DB_CONNECTION=sqlite/' .env
sed -i 's/DB_DATABASE=task_tracker/DB_DATABASE=database\/database.sqlite/' .env

# Run migrations
echo "📊 Running database migrations..."
php artisan migrate

# Seed database
echo "🌱 Seeding database with sample data..."
php artisan db:seed

# Install Laravel Breeze (silently)
echo "🔐 Setting up authentication..."
php artisan breeze:install blade --dark

# Compile assets
echo "🎨 Compiling assets..."
npm run build

# Set permissions
echo "🔧 Setting permissions..."
chmod -R 755 storage bootstrap/cache

echo "✅ Setup complete!"
echo ""
echo "🚀 To start the development server:"
echo "   php artisan serve"
echo ""
echo "📱 Your application will be available at:"
echo "   http://localhost:8000"
echo ""
echo "🔑 Default login credentials (if demo data was seeded):"
echo "   Email: admin@tasktracker.com"
echo "   Password: password"
```

## 📚 Development Commands

### Daily Development
```bash
# Start development server
php artisan serve

# Watch for file changes
npm run dev

# Start queue worker
php artisan queue:work

# Clear caches during development
php artisan optimize:clear
```

### Database Management
```bash
# Create new migration
php artisan make:migration create_table_name

# Rollback last migration
php artisan migrate:rollback

# Fresh migration with seeding
php artisan migrate:fresh --seed

# Create model with migration and factory
php artisan make:model ModelName -mf
```

### Livewire Commands
```bash
# Create new Livewire component
php artisan make:livewire ComponentName

# Create Livewire component with inline view
php artisan make:livewire ComponentName --inline

# Publish Livewire config
php artisan livewire:publish --config
```

### Testing
```bash
# Run all tests
php artisan test

# Run specific test
php artisan test --filter=TrackerTest

# Run tests with coverage
php artisan test --coverage
```

## 🐳 Docker Setup (Optional)

```yaml
# docker-compose.yml
version: '3.8'
services:
  app:
    build: .
    ports:
      - "8000:8000"
    volumes:
      - .:/var/www/html
    depends_on:
      - mysql
      - redis

  mysql:
    image: mysql:8.0
    environment:
      MYSQL_DATABASE: task_tracker
      MYSQL_ROOT_PASSWORD: secret
    ports:
      - "3306:3306"

  redis:
    image: redis:alpine
    ports:
      - "6379:6379"
```

## 🎯 Next Phase

After successful setup, we'll move to **Phase 2: Core Implementation**:
1. Database migrations
2. Eloquent models with relationships
3. Repository pattern implementation
4. Service layer development
5. Livewire components creation

The setup provides a solid foundation with all necessary dependencies and proper project structure for building the beautiful task tracker application.
