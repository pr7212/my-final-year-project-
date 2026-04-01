# 🗑️ Garbage Management & Tracking System

A full-stack web application for reporting, tracking, and managing garbage collection requests in a city or community. The system provides citizens with a portal to submit waste pickup requests and allows administrators to manage and update those requests in real time.

---

## 📌 Table of Contents

- [Overview](#overview)
- [Project Structure](#project-structure)
- [Tech Stack](#tech-stack)
- [Features](#features)
- [Database Schema](#database-schema)
- [Getting Started](#getting-started)
  - [Prerequisites](#prerequisites)
  - [Installation](#installation)
  - [Running the App](#running-the-app)
- [Usage](#usage)
- [API Endpoints](#api-endpoints)
- [Security](#security)
- [Screenshots](#screenshots)
- [Contributing](#contributing)
- [License](#license)

---

## 📖 Overview

The **Garbage Management & Tracking System** is a two-part project:

| Part                   | Folder             | Description                              |
| ---------------------- | ------------------ | ---------------------------------------- |
| **Frontend Prototype** | `garbage-system/`  | Static HTML/CSS/JS mockup of the UI      |
| **Full Backend App**   | `garbage-tracker/` | PHP + MySQL production-ready application |

Citizens can register, log in, submit garbage pickup requests, and track their status. Admins can assign trucks, update statuses, and manage all requests through a responsive dashboard.

---

## 📁 Project Structure

```
garbage_system/
│
├── garbage-system/                 # Static frontend prototype
│   ├── index.html                  # Landing page
│   ├── login.html                  # Login UI
│   ├── register.html               # Registration UI
│   ├── dashboard.html              # Dashboard UI with stats & reports table
│   ├── use-case-diagram.html       # System use-case diagram
│   ├── css/
│   │   └── style.css               # Frontend styles
│   └── js/
│       └── script.js               # Frontend JavaScript logic
│
├── garbage-tracker/                # Full PHP backend application
│   ├── index.php                   # Login page (entry point)
│   ├── dashboard.php               # Main dashboard with CRUD (AJAX)
│   ├── register.php                # User registration page
│   ├── start.bat                   # Windows quick-start script (XAMPP)
│   │
│   ├── actions/                    # Backend API action handlers
│   │   ├── login.php               # Handles login POST
│   │   ├── logout.php              # Ends session & redirects
│   │   ├── register_user.php       # Handles registration POST
│   │   ├── create_request.php      # Creates a new garbage request
│   │   ├── fetch_requests.php      # Fetches all requests (AJAX)
│   │   ├── edit_request.php        # Updates an existing request
│   │   ├── delete_request.php      # Deletes a request
│   │   ├── update_status.php       # Updates request status
│   │   └── assign_truck.php        # Assigns a truck to a request
│   │
│   ├── config/
│   │   └── db.php                  # MySQL database connection config
│   │
│   ├── database/
│   │   └── schema.sql              # Full database schema (import this first)
│   │
│   ├── includes/                   # Shared PHP partials
│   │   ├── header.php              # HTML <head> + navbar partial
│   │   ├── footer.php              # HTML closing tags + footer
│   │   └── auth.php                # Session authentication guard
│   │
│   ├── css/
│   │   └── style.css               # Backend app styles
│   │
│   ├── js/
│   │   └── script.js               # AJAX logic (CRUD, table loading)
│   │
│   └── tests/                      # PHP test scripts
│       ├── test_login.php
│       ├── test_dashboard.php
│       └── test_crud.php
│
├── TODO.md                         # Project task tracker
└── README.md                       # This file
```

---

## 🛠️ Tech Stack

| Layer        | Technology                                         |
| ------------ | -------------------------------------------------- |
| **Frontend** | HTML5, CSS3, JavaScript (Vanilla)                  |
| **Backend**  | PHP 8+                                             |
| **Database** | MySQL                                              |
| **Icons**    | Font Awesome 6                                     |
| **Server**   | XAMPP (Apache + PHP) / PHP Built-in Server         |
| **Security** | CSRF Tokens, Prepared Statements, XSS Sanitization |

---

## ✨ Features

### 👤 User Features

- ✅ User registration with email & password
- ✅ Secure login/logout with session management
- ✅ Submit garbage pickup requests with location/area
- ✅ View personal request history in a dashboard table
- ✅ Edit and delete own requests
- ✅ Real-time table refresh via AJAX (no page reload)

### 🔧 Admin Features

- ✅ View all citizen requests
- ✅ Update request status (`pending` → `in-progress` → `completed`)
- ✅ Assign trucks to specific requests
- ✅ Delete or modify any request

### 📊 Dashboard

- ✅ Stats cards: Total Reports, Pending, Collected
- ✅ Responsive sidebar navigation
- ✅ Searchable reports table
- ✅ Inline edit modal for requests

---

## 🗄️ Database Schema

The application uses a MySQL database named `garbage_tracker` with the following tables:

### `users`

| Column       | Type         | Description            |
| ------------ | ------------ | ---------------------- |
| `id`         | INT (PK)     | Auto-increment user ID |
| `name`       | VARCHAR(100) | Full name              |
| `email`      | VARCHAR(100) | Unique email address   |
| `password`   | VARCHAR(255) | Hashed password        |
| `role`       | ENUM         | `user` or `admin`      |
| `created_at` | TIMESTAMP    | Account creation time  |

### `requests`

| Column      | Type         | Description                           |
| ----------- | ------------ | ------------------------------------- |
| `id`        | INT (PK)     | Auto-increment request ID             |
| `user_id`   | INT (FK)     | References `users.id`                 |
| `area`      | VARCHAR(255) | Pickup location/area                  |
| `status`    | ENUM         | `pending`, `in-progress`, `completed` |
| `timestamp` | TIMESTAMP    | Request creation time                 |

### `reports`

| Column        | Type         | Description              |
| ------------- | ------------ | ------------------------ |
| `id`          | INT (PK)     | Auto-increment report ID |
| `user_id`     | INT (FK)     | References `users.id`    |
| `location`    | VARCHAR(255) | Waste location           |
| `description` | TEXT         | Detailed description     |
| `status`      | ENUM         | `pending` or `collected` |
| `created_at`  | TIMESTAMP    | Report creation time     |

### `schedules`

| Column            | Type         | Description                |
| ----------------- | ------------ | -------------------------- |
| `id`              | INT (PK)     | Auto-increment schedule ID |
| `location`        | VARCHAR(255) | Collection area            |
| `collection_date` | DATE         | Scheduled date             |
| `status`          | ENUM         | `scheduled` or `done`      |

---

## 🚀 Getting Started

### Prerequisites

Make sure you have the following installed:

- [XAMPP](https://www.apachefriends.org/) (includes PHP 8+ and MySQL) **or**
- PHP 8+ and MySQL separately
- A web browser (Chrome, Firefox, Edge, etc.)

---

### Installation

**Step 1 — Clone or download the repository**

```bash
git clone https://github.com/pr7212/my-final-year-project-.git
cd garbage_system
```

**Step 2 — Set up the database**

1. Open [phpMyAdmin](http://localhost/phpmyadmin) or your MySQL client
2. Create a new database called `garbage_tracker` (or let the script do it)
3. Import the schema:

```bash
mysql -u root -p garbage_tracker < garbage-tracker/database/schema.sql
```

**Step 3 — Configure the database connection**

Open `garbage-tracker/config/db.php` and update the credentials:

```php
$servername = "localhost";
$username   = "root";       // your MySQL username
$password   = "";           // your MySQL password
$dbname     = "garbage_tracker";
```

---

### Running the App

#### Option A — Using `start.bat` (Windows / XAMPP)

```batch
cd garbage-tracker
start.bat
```

> Opens a PHP development server at **http://localhost:8000**

#### Option B — PHP Built-in Server (any OS)

```bash
cd garbage-tracker
php -S localhost:8000
```

Then open **http://localhost:8000/index.php** in your browser.

#### Option C — XAMPP Apache

1. Copy the `garbage-tracker/` folder to your `htdocs/` directory
2. Start Apache and MySQL in XAMPP Control Panel
3. Navigate to **http://localhost/garbage-tracker/**

---

## 📋 Usage

1. **Open the app** at `http://localhost:8000`
2. **Register** a new account (`register.php`)
3. **Login** with your email and password
4. You will be redirected to the **Dashboard**
5. **Create a request** by entering your area/location and submitting
6. **Manage requests** — edit status, delete, or view history in the table
7. Click **Refresh Table** to reload requests via AJAX
8. **Logout** using the logout link in the header

---

## 🔌 API Endpoints

All action handlers live in `garbage-tracker/actions/`:

| File                 | Method | Description                         |
| -------------------- | ------ | ----------------------------------- |
| `login.php`          | POST   | Authenticate user, start session    |
| `logout.php`         | GET    | Destroy session, redirect to login  |
| `register_user.php`  | POST   | Create new user account             |
| `create_request.php` | POST   | Submit a new garbage pickup request |
| `fetch_requests.php` | GET    | Return all requests as JSON (AJAX)  |
| `edit_request.php`   | POST   | Update area/status of a request     |
| `delete_request.php` | POST   | Delete a specific request           |
| `update_status.php`  | POST   | Change the status of a request      |
| `assign_truck.php`   | POST   | Assign a truck to a request         |

---

## 🔐 Security

The application implements multiple layers of security:

| Protection            | Implementation                                                |
| --------------------- | ------------------------------------------------------------- |
| **CSRF Protection**   | Hidden `csrf_token` field in all forms, validated server-side |
| **SQL Injection**     | All queries use **prepared statements** (`mysqli`)            |
| **XSS Prevention**    | All outputs sanitized with `htmlspecialchars()`               |
| **Session Auth**      | Protected pages redirect to login via `auth.php` guard        |
| **Password Security** | Passwords hashed using PHP `password_hash()`                  |

---

## 🧪 Tests

Run the included PHP test scripts to verify core functionality:

```bash
cd garbage-tracker

# Test login functionality
php tests/test_login.php

# Test dashboard loading
php tests/test_dashboard.php

# Test full CRUD cycle
php tests/test_crud.php
```

---

## 🚢 Deployment (Production)

To deploy on a live server:

1. **Web Server:** Use **Apache** or **Nginx** with **PHP 8+** and **MySQL**
2. **Document Root:** Point it to `garbage-tracker/`
3. **Database:** Import `database/schema.sql` on your production MySQL server
4. **Config:** Update credentials in `config/db.php`
5. **HTTPS:** Enable SSL/TLS for secure connections
6. **Environment:** Move DB credentials to environment variables for production

---

## 🤝 Contributing

Contributions, issues and feature requests are welcome!

1. Fork the project
2. Create a feature branch: `git checkout -b feature/my-feature`
3. Commit your changes: `git commit -m 'Add my feature'`
4. Push to the branch: `git push origin feature/my-feature`
5. Open a Pull Request

---

## 📄 License

This project is open source and available under the [MIT License](LICENSE).

---

## 👨‍💻 Author

**Final Year Project** — Built with ❤️ to help communities manage waste efficiently.

> _"A clean environment is a sign of a healthy society."_
