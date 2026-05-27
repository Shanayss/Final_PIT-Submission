# WellMeadows Hospital Management System

A role-based Hospital Management System built with Laravel (PHP), Blade templates, and a mix of Tailwind CSS and custom CSS/JS.

---

## Features 
- Multiple staff roles (Medical Director, Personnel Officer, Clinical Staff, Nursing Staff, Cashier)
- Auth/login and session-based role routing
- Medical Director: 
- Nursing workflows: admit/register patients, assign beds, discharge patients, medication schedules, update patient conditions, ward occupancy, and care notes
- Cashier workflows: billing, payments, reports


---

## Tech Stack

### Frontend
- **Blade (Laravel views)**
- **Tailwind CSS** 
- **JavaScript** 
- **Custom CSS** 

### Backend
- **Laravel**
- **Controllers/Routes**
- **Database access** 
- **PostgreSQL** 

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
php artisan migrate 
php artisan db:seed
```

### 5) Install & build frontend assets
```bash
npm install
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
- `routes/web.php` - web routes and role routing
- `app/Http/Controllers/` - application controllers
- `resources/views/` - Blade UI pages 
- `public/js/` - custom JavaScript for dashboards
- `public/css/` - custom CSS styling
- `database/migrations/` - schema migrations (including Postgres-specific ones)

---

## Notes
- Tailwind is configured, but some pages (especially dashboards) use custom CSS from `public/css/*`.


