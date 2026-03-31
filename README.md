# Garbage Management System

## Local Setup Guide (XAMPP)

This repository contains a Laravel 12 application for garbage management, using:

- Laravel 12
- Laravel Breeze (Authentication)
- Livewire
- Tailwind CSS
- MySQL (via XAMPP)
- Vite

---

## Requirements

- XAMPP (PHP, MySQL/MariaDB, Apache)
- Composer
- Node.js (LTS)

---

## 1. Install Required Software

### 1.1 Install XAMPP

Download and install XAMPP, which includes:

- PHP
- MySQL / MariaDB
- Apache

Then start the following services from the XAMPP Control Panel:

- Apache ✅
- MySQL ✅

### 1.2 Install Composer

Download Composer from:

https://getcomposer.org/download/

Verify installation:

```bash
composer -V
```

### 1.3 Install Node.js

Download the Node.js LTS version from:

https://nodejs.org

Verify installation:

```bash
node -v
npm -v
```

---

## 2. Clone the Project

Open a terminal or Git Bash and run:

```bash
git clone <repo-url>
cd garbage-management-system
```

> Replace `<repo-url>` with the actual repository URL.

---

## 3. Install PHP Dependencies

If you are using XAMPP PHP, install dependencies with:

```bash
composer install
```

If Composer cannot find PHP, use the XAMPP PHP executable directly:

```bash
C:\xampp\php\php.exe composer install
```

---

## 4. Configure Environment

Copy the example environment file:

```bash
cp .env.example .env
```

Generate the application key:

```bash
php artisan key:generate
```

---

## 5. Configure Database

Open phpMyAdmin at:

http://localhost/phpmyadmin

Create a new database named:

- `garbage_system`

Update your `.env` file with the database settings:

```env
DB_CONNECTION=mysql
DB_HOST=127.0.0.1
DB_PORT=3306
DB_DATABASE=garbage_system
DB_USERNAME=root
DB_PASSWORD=
```

> XAMPP default MySQL password is empty.

---

## 6. Run Database Migrations

Apply migrations:

```bash
php artisan migrate
```

If seeders are available:

```bash
php artisan db:seed
```

---

## 7. Install Frontend Dependencies

Install Node dependencies:

```bash
npm install
```

Start the Vite development server:

```bash
npm run dev
```

Keep this terminal open while developing.

---

## 8. Run the Application

Open a second terminal and start Laravel:

```bash
php artisan serve
```

Then visit:

```text
http://127.0.0.1:8000
```

---

## 9. Daily Development Workflow

Use two terminals during development:

1. `php artisan serve`
2. `npm run dev`

---

## 10. Troubleshooting

### Permission errors (Windows)

Run:

```bash
php artisan optimize:clear
```

### Reset the application

If the project becomes unstable, reset it with:

```bash
php artisan optimize:clear
php artisan migrate:fresh --seed
```
