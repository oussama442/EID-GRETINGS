# Car Rental Management

Laravel + Filament application for running a car rental operation with a public booking site, admin dashboard, mobile API, fleet tracking, customer records, payments, contracts, and branch-level access control.

## Core Features

- Public fleet browsing with search, filters, date availability, and booking request forms.
- Filament admin panel for cars, bookings, clients, branches, payments, settings, users, revenue reports, and operational alerts.
- Mobile API for staff login, dashboard, cars, clients, booking creation, check-in, and check-out.
- Booking conflict protection across public requests, mobile API, and Filament booking forms.
- Car lifecycle protection for reserved, active, overdue, completed, cancelled, maintenance, and out-of-service states.
- PDF contracts, receipts, and revenue reports.
- Multi-language public/admin setup with English, French, and Arabic language files.
- GitHub Actions CI for backend tests and frontend build.

## Tech Stack

- PHP 8.2+
- Laravel 12
- Filament 5
- Laravel Sanctum
- Spatie Laravel Permission
- DomPDF
- Tailwind CSS 4
- Vite

## Local Setup

```bash
composer install
cp .env.example .env
php artisan key:generate
touch database/database.sqlite
php artisan migrate --seed
npm install
npm run build
php artisan serve
```

Open the app at:

```text
http://127.0.0.1:8000
```

Admin panel:

```text
http://127.0.0.1:8000/admin
```

Default seeded admin:

```text
Email: admin@example.com
Password: password
```

## Useful Commands

Run tests:

```bash
php artisan test
```

Build assets:

```bash
npm run build
```

Run the full local dev stack:

```bash
composer run dev
```

Health check:

```text
GET /health
```

Expected response:

```json
{
  "status": "ok",
  "app": "car-rental"
}
```

## Mobile API

Authentication:

```text
POST /api/login
GET /api/me
POST /api/logout
```

Operations:

```text
GET /api/dashboard
GET /api/cars
GET /api/cars/{id}
PUT /api/cars/{id}/status
GET /api/clients
POST /api/clients
GET /api/bookings
POST /api/bookings
POST /api/bookings/{id}/check-in
POST /api/bookings/{id}/check-out
```

The API accepts availability filters on cars:

```text
GET /api/cars?pickup_datetime=2026-07-10 10:00:00&return_datetime=2026-07-12 10:00:00
```

## Booking Rules

- A car cannot be booked for overlapping reserved, active, or overdue periods.
- A rented car cannot be manually marked available while active or overdue bookings exist.
- Reserved bookings must be checked in before they can be checked out.
- Completed bookings cannot be checked in again.
- On check-out, the car becomes reserved if another future reservation exists, otherwise available.
- Maintenance and out-of-service states are preserved until changed by staff.

## Deployment Notes

- Use a real database in production, not SQLite unless the hosting setup is intentionally single-node.
- Set `APP_ENV=production`, `APP_DEBUG=false`, and a stable `APP_KEY`.
- Run `php artisan storage:link` if car/client images are stored on the public disk.
- Run queue workers if reminders, notifications, or async tasks are added.
- Configure backups for the database and `storage/app/public`.
- Point uptime monitoring at `/health`.

## Security Notes

- Never commit `.env`, `auth.json`, storage keys, logs, uploads, `vendor`, or `node_modules`.
- Keep admin accounts limited to trusted users.
- Use branch-restricted agent accounts for staff who should only see one location.
- Rotate credentials after moving from local development to production.

## Repository Status

Current verification:

```text
php artisan test
13 tests / 66 assertions passing

npm run build
passing
```
