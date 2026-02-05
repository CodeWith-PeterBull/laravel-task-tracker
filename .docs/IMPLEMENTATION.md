# Task Tracker Application - Implementation Document

## Technology Stack

| Component          | Technology            | Version |
|--------------------|-----------------------|---------|
| Backend Framework  | Laravel               | 12.x   |
| Frontend Reactivity| Livewire (Volt)       | 4.1.x  |
| CSS Framework      | Tailwind CSS          | 4.x    |
| Authentication     | Laravel Breeze        | 2.x    |
| Permissions        | Spatie Laravel Permission | 6.x |
| Database           | SQLite                | Default |
| Build Tool         | Vite                  | 7.x    |
| PHP                | PHP                   | 8.4    |

## Architecture Overview

### Database Schema

```
users
├── id (PK)
├── name
├── email (unique)
├── email_verified_at
├── password
├── remember_token
└── timestamps

categories
├── id (PK)
├── user_id (FK → users)
├── name
├── color (#hex)
├── icon (nullable)
├── sort_order
└── timestamps
└── UNIQUE(user_id, name)

tasks
├── id (PK)
├── user_id (FK → users)
├── category_id (FK → categories, nullable)
├── title
├── description (nullable)
├── priority (1=low, 2=medium, 3=high)
├── due_date (date)
├── is_completed (boolean)
├── completed_at (timestamp, nullable)
├── sort_order
├── is_recurring (boolean)
├── recurrence_pattern (nullable)
└── timestamps
└── INDEX(user_id, due_date)
└── INDEX(user_id, is_completed)

tags
├── id (PK)
├── user_id (FK → users)
├── name
├── color (#hex)
└── timestamps
└── UNIQUE(user_id, name)

task_tag (pivot)
├── id (PK)
├── task_id (FK → tasks)
├── tag_id (FK → tags)
└── timestamps
└── UNIQUE(task_id, tag_id)
```

### Spatie Roles & Permissions

**Roles:**
- `admin` - Full access to all features including user management
- `user` - Standard access to task management and statistics

**Permissions:**
- `view tasks`, `create tasks`, `edit tasks`, `delete tasks`
- `manage categories`, `manage tags`
- `view statistics`
- `manage users` (admin only)

### Livewire Volt Components

All components use Livewire 4's Volt functional API (single-file components with PHP + Blade).

#### 1. Dashboard (`resources/views/components/⚡dashboard.blade.php`)
- Welcome header with user greeting and date
- Summary stats: Today, This Week, Streak, Overdue
- Today's progress bar
- Overdue tasks list (with completion toggle)
- Upcoming tasks list
- Category completion overview

#### 2. TaskManager (`resources/views/components/⚡task-manager.blade.php`)
- **Calendar Navigation Bar**: Gradient header with month/year, date picker, navigation arrows
- **Day Selector Strip**: 7-day strip centered on selected date with task counts
- **Progress Bar**: Day completion percentage with animated fill
- **Filter Bar**: Search, status filter, category filter, priority filter
- **Task Form**: Create/Edit with title, description, priority, category, due date, tags
- **Task List**: Checkbox toggle, priority indicator, category/tag badges, edit/delete actions

#### 3. Statistics (`resources/views/components/⚡statistics.blade.php`)
- Period selector (7/14/30/60/90 days)
- Summary cards: Total tasks, Completed, Completion rate, Avg tasks/day
- **Activity Heatmap**: 90-day GitHub-style heatmap with hover tooltips
- **Completion Trend**: CSS bar chart with color-coded completion rates
- **Priority Distribution**: Horizontal bar chart (High/Medium/Low)
- **Category Breakdown**: Progress bars per category
- **Weekday Performance**: Vertical bar chart by day of week
- **Tag Usage**: Tag cloud with usage counts

## File Structure (Key Files)

```
app/
├── Models/
│   ├── User.php          (HasRoles, tasks/categories/tags relations)
│   ├── Task.php           (scopes, priority accessors, belongs-to-many tags)
│   ├── Category.php       (has-many tasks)
│   └── Tag.php            (belongs-to-many tasks)
database/
├── migrations/
│   ├── create_permission_tables.php
│   ├── create_categories_table.php
│   ├── create_tasks_table.php
│   ├── create_tags_table.php
│   └── create_task_tag_table.php
├── factories/
│   ├── CategoryFactory.php
│   ├── TaskFactory.php
│   └── TagFactory.php
├── seeders/
│   ├── DatabaseSeeder.php
│   ├── RoleAndPermissionSeeder.php
│   ├── CategorySeeder.php
│   ├── TagSeeder.php
│   └── TaskSeeder.php
resources/views/
├── components/
│   ├── ⚡dashboard.blade.php
│   ├── ⚡task-manager.blade.php
│   └── ⚡statistics.blade.php
├── layouts/
│   └── app.blade.php
├── livewire/layout/
│   └── navigation.blade.php
├── dashboard.blade.php
├── tasks.blade.php
├── statistics.blade.php
└── welcome.blade.php
routes/
└── web.php
```

## Seeded Test Data

### Users
| Email               | Password  | Role  |
|---------------------|-----------|-------|
| admin@example.com   | password  | admin |
| test@example.com    | password  | user  |
| demo@example.com    | password  | user  |

### Categories (per user)
Work, Personal, Health, Learning, Finance, Errands

### Tags (per user)
urgent, important, review, follow-up, quick-win, deep-work, meeting, deadline

### Tasks
- ~250+ tasks per user spread across 90 days (-60 to +30 from today)
- 0-6 tasks per day, randomly distributed
- 75% completion rate for past tasks, 20% for future tasks
- Random priority, category, and 1-3 tags per task

## Setup Instructions

```bash
# Clone and install
git clone <repo-url>
cd laravel-task-tracker

# Install dependencies
composer install
npm install

# Environment setup
cp .env.example .env
php artisan key:generate

# Database (SQLite is default)
touch database/database.sqlite
php artisan migrate --seed

# Build assets
npm run build

# Serve
php artisan serve
```

## Design Decisions

1. **Livewire 4 Volt**: Chosen over class-based components for simpler, self-contained files. Each component is a single `.blade.php` file with embedded PHP logic.

2. **SQLite**: Default for simplicity. No external database server needed. Can be swapped to MySQL/PostgreSQL via `.env`.

3. **CSS-only charts**: No JavaScript charting library required. Heatmaps, bar charts, and progress bars are built with pure Tailwind CSS for a lightweight experience.

4. **User-scoped data**: All queries are scoped to `auth()->id()` ensuring data isolation between users.

5. **Computed properties**: Livewire's `get*Property()` pattern used for reactive data that recalculates on each render, keeping the component class clean.
