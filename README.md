# FastTrack

FastTrack is a delivery-order management application for customers, couriers, and administrators. It supports role-based dashboards, order creation and tracking, courier status updates, product management, in-order messaging, notifications, address validation, and delivery fee calculation.

## Tech Stack

- Laravel 12
- React 19
- Inertia
- Tailwind CSS 4
- SQLite
- Vite

## Setup

1. Install PHP dependencies: `composer install`
2. Copy `.env.example` to `.env`, then set `DB_CONNECTION=sqlite`
3. Create the SQLite database: `touch database/database.sqlite`
4. Generate the application key and migrate: `php artisan key:generate && php artisan migrate`
5. Install frontend dependencies: `npm install`
6. Start the development environment: `composer run dev`

## State Machine

See [STATE_MACHINE_VISUAL_REFERENCE.md](STATE_MACHINE_VISUAL_REFERENCE.md) for the delivery lifecycle and valid state transitions.

## Screenshots

Screenshots placeholder: add application screenshots here.
