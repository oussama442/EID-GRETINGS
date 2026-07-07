# Deployment Checklist

Use this checklist before putting the car rental app on a live server.

## Server

- PHP 8.2 or newer
- Composer 2
- Node.js 22 or newer for asset builds
- MySQL or PostgreSQL recommended for production
- Web server points to the `public/` directory
- HTTPS enabled

## Environment

Set these values in `.env`:

```env
APP_NAME="Car Rental"
APP_ENV=production
APP_DEBUG=false
APP_URL=https://your-domain.com

DB_CONNECTION=mysql
DB_HOST=127.0.0.1
DB_PORT=3306
DB_DATABASE=car_rental
DB_USERNAME=car_rental_user
DB_PASSWORD=change-this

SESSION_DRIVER=database
QUEUE_CONNECTION=database
FILESYSTEM_DISK=public
```

## First Deploy

```bash
composer install --no-dev --optimize-autoloader
npm ci
npm run build
php artisan key:generate
php artisan migrate --force
php artisan storage:link
php artisan config:cache
php artisan route:cache
php artisan view:cache
```

Seed demo data only on staging or local machines:

```bash
php artisan db:seed
```

## Update Deploy

```bash
git pull
composer install --no-dev --optimize-autoloader
npm ci
npm run build
php artisan migrate --force
php artisan config:cache
php artisan route:cache
php artisan view:cache
```

## After Deploy Checks

- `GET /health` returns status `ok`.
- Admin login works at `/admin`.
- Public fleet page loads.
- Booking request validation works.
- Car images load from public storage.
- Mobile API login returns a token.

## Backups

Back up these on a schedule:

- production database
- `storage/app/public`
- `.env` stored securely outside git

## Rollback

Keep the previous release or commit available. If a deploy fails:

```bash
git reset --hard <previous-good-commit>
composer install --no-dev --optimize-autoloader
npm ci
npm run build
php artisan migrate --force
php artisan config:cache
php artisan route:cache
php artisan view:cache
```
