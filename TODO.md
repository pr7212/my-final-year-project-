# TODO: Test register/login → dashboard shows DB data

## Plan Summary

Test flow: register new user → login → dashboard displays data from DB (after creating requests).

**Status:** In progress

## Steps

### 1. [DONE ✅] Fix authentication protection

Updated `garbage-tracker/includes/auth.php` with session_start() + login check/redirect to index.php.

### 2. [MANUAL] Setup Database

**Manual step - run in new terminal:**

```
cd /d "c:\Users\Administrator\Desktop\garbage_system\garbage-tracker\database"
C:\xampp\mysql\bin\mysql.exe -u root < schema.sql

```

(Starts MySQL if not, creates DB/tables. No password for root.)

Or start XAMPP Control Panel → Start MySQL → then above.

### 3. [PENDING] Start PHP Development Server

Run `cd garbage-tracker && start.bat` (starts server at http://localhost:8000)

### 4. [PENDING] Test Full Flow

- Open http://localhost:8000/index.php
- Click Register → create user (name, email, pass)
- Login at index.php
- Go to dashboard.php → table loads (empty OK)
- Create request (area) → refresh → see DB data in table

### 5. [PENDING] Verify & Complete

**Next Action:** Start server and test flow (Steps 3-4)
