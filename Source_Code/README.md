# WellMeadows Hospital Management System

A role-based Hospital Management System built with **Laravel (PHP)**, **Blade** templates, and a mix of **Tailwind CSS** + custom CSS/JS.

---

## Features (high level)
- Multiple staff roles (Medical Director, Personnel Officer, Clinical Staff, Nursing Staff, Cashier)
- Nursing workflows: admit/register patients, assign beds, discharge patients, medication schedules, update patient conditions, ward occupancy, and care notes
- Cashier/Medical Director workflows and reports (billing, payments, reports)
- Auth/login and session-based role routing

---

## Tech Stack

### Frontend
- **Blade (Laravel views)**
- **Tailwind CSS** (configured in `tailwind.config.js`, plus `@tailwindcss/forms`)
- **Alpine.js** (loaded in `resources/views/layouts/app.blade.php`)
- **Vite** for bundling front-end assets
- **JavaScript** (module-based + custom scripts in `public/js/*`)
- **Font Awesome** (CDN)
- **Custom CSS** (e.g., `public/css/dashboard.css`, `public/css/NursingStaff/nursing-staff.css`)

### Backend
- **PHP 8.2+**
- **Laravel v12**
- **Controllers/Routes**: `app/Http/Controllers/*`, `routes/web.php`
- **Database access** via Eloquent/Query Builder
- **PostgreSQL** (there are Postgres-specific migration files under `database/migrations/postgre/*`)

---

## Prerequisites
- PHP **8.2+**
- Composer
- Node.js + npm
- PostgreSQL (or your configured DB)

---

## Setup & Run (local)

### 1) Install PHP dependencies
```bash
composer install
```

### 2) Configure environment
```bash
cp .env.example .env
```
Update database credentials in `.env`.

### 3) Generate app key
```bash
php artisan key:generate
```

### 4) Run migrations + seeders
```bash
php artisan migrate --force
php artisan db:seed
```

### 5) Install & build frontend assets
```bash
npm install
npm run dev
```

### 6) Start the server
```bash
php artisan serve
```

---

## Useful npm / build commands
- Dev mode: `npm run dev`
- Production build: `npm run build`

---

## Project Structure (quick guide)
- `routes/web.php` — web routes and role routing
- `app/Http/Controllers/` — application controllers
- `resources/views/` — Blade UI pages (role-specific dashboards under `resources/views/<Role>/*`)
- `public/js/` — custom JavaScript for dashboards
- `public/css/` — custom CSS styling
- `database/migrations/` — schema migrations (including Postgres-specific ones)

---

## Notes
- Tailwind is configured, but some pages (especially dashboards) use custom CSS from `public/css/*`.

---

## License
MIT (framework licensing)

