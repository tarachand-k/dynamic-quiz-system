# Dynamic Quiz System

A Laravel application for creating and sharing quizzes with multiple question types,
media support, and automatic scoring.

## Documentation

- [Architecture](ARCHITECTURE.md)
- [AI Usage](AI_USAGE.md)

## Requirements

- PHP 8.3+
- Composer
- Node.js (v20+) & npm

## Setup

```bash
composer install
cp .env.example .env
php artisan key:generate
php artisan migrate --seed
npm install && npm run build
php artisan storage:link
php artisan serve
```

The seeder creates an admin account and two sample quizzes with attempts so the
dashboard is not empty on first login.

**Login credentials:**

- Email: `admin@wozku.com`
- Password: `password`

## Question Types

| Type            | Description                                                    |
|-----------------|----------------------------------------------------------------|
| Binary          | Yes / No question                                              |
| Single Choice   | One correct option from a list                                 |
| Multiple Choice | One or more correct options                                    |
| Number Input    | Numeric answer with optional tolerance                         |
| Text Input      | Text answer with exact, case-insensitive, or contains matching |

## Notes

- Media files are stored using Laravel's local public disk (`storage/app/public`)
- `php artisan storage:link` is required to serve uploaded images
- Authentication is handled by Laravel Breeze — registration is disabled, access is via the seeded admin account
- Quiz attempt pages are public and shareable without login
