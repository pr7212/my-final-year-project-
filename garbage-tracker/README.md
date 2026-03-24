# Garbage Tracker - Complete System

## Features

- User registration/login (citizen/admin roles)
- Dashboard with full CRUD for garbage pickup requests
- AJAX real-time table updates
- Client/server validation & CSRF protection
- Responsive design
- Prepared statements (SQL injection safe)
- XSS protection

## Quick Start (Localhost)

1. **Database:** Import `database/schema.sql`
2. **Start server:** `cd garbage-tracker && php -S localhost:8000`
3. **Test:** Open http://localhost:8000/index.php
4. **Register/Login** -> Dashboard CRUD

## Tests

```bash
cd garbage-tracker
php tests/test_login.php      # Read test instructions
php tests/test_dashboard.php
php tests/test_crud.php
```

## Deployment

- Use Apache/Nginx + PHP 8+ + MySQL
- Set document root to `garbage-tracker/`
- Update DB credentials in `config/db.php`

## File Structure (Phase 5 Complete)

```
garbage-tracker/
├── index.php          # Login
├── dashboard.php      # CRUD Dashboard (AJAX)
├── register.php
├── actions/*.php      # APIs (CRUD + auth)
├── js/script.js       # Frontend logic
├── css/style.css      # Styles
├── includes/          # Header/footer/auth
├── config/db.php
└── tests/             # Test scripts
```

**Phase 5 COMPLETE** ✅
