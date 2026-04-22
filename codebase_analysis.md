# Garbage Tracker — Codebase Analysis

## Overview

A PHP + MySQL CRUD web application for managing garbage collection requests. Users interact with a shared UI backed by role-gated PHP action scripts and a single MySQL database (`garbage_tracker`).

---

## Architecture

```
garbage-tracker/
├── index.php            # Login page
├── register.php         # Registration page
├── dashboard.php        # Role-based redirect hub
├── admin.php            # Admin dashboard (requests)
├── admin_schedules.php  # Admin: schedules management
├── admin_trucks.php     # Admin: truck management
├── admin_users.php      # Admin: user management
├── admin_reports.php    # Admin: reports/complaints view
├── resident.php         # Resident dashboard
├── collector.php        # Collector dashboard
├── officer.php          # Officer dashboard (read-only)
├── config/db.php        # DB connection (MySQLi)
├── includes/
│   ├── auth.php         # Session guard + requireRole()
│   ├── header.php       # HTML head + navbar
│   └── footer.php       # Closing tags
├── actions/             # AJAX / form POST handlers
│   ├── login.php
│   ├── logout.php
│   ├── register_user.php
│   ├── create_request.php
│   ├── edit_request.php
│   ├── delete_request.php
│   ├── fetch_requests.php
│   ├── fetch_areas.php
│   ├── fetch_reports.php
│   ├── fetch_schedules.php
│   ├── fetch_trucks.php
│   ├── fetch_users.php
│   ├── create_report.php
│   ├── create_schedule.php
│   ├── create_truck.php
│   ├── create_user.php
│   ├── assign_truck.php
│   ├── update_status.php
│   └── update_truck_status.php
├── database/schema.sql  # DB schema + seed data
├── css/style.css
└── js/script.js
```

---

## Roles

| Role | Access |
|------|--------|
| `resident` | Submit/edit/delete own pending requests; submit complaints |
| `collector` | View assigned jobs; mark as collected |
| `officer` | Read-only view of all requests |
| `admin` | Full CRUD on requests, trucks, schedules, users, reports |

---

## What's Working Well ✅

- **CSRF protection** on all mutating actions (login, register, create, edit, delete, update_status, logout) using `hash_equals()` — correct timing-safe comparison.
- **Prepared statements** with `bind_param` used everywhere — no raw SQL string concatenation.
- **Password hashing** with `password_hash` / `password_verify` — no plaintext passwords.
- **Session regeneration** on login (`session_regenerate_id(true)`).
- **Role-based access control** enforced server-side in every action file.
- **Input sanitization** — `htmlspecialchars()` applied to all output.
- **Responsive CSS** — mobile card layout via `@media (max-width: 768px)` with stacked `<td>` cells.
- **Status badges** defined as CSS classes (`.status-pending`, `.status-completed`, etc.).
- **`dashboard.php`** acts as a clean redirect hub post-login.

---

## Bugs & Issues Found 🐛

### 🔴 Critical

#### 1. `database/schema.sql` — Duplicate column + duplicate constraint
```sql
-- Line 68-69: truck_id declared TWICE
truck_id INT DEFAULT NULL,
truck_id INT DEFAULT NULL,   -- ← DUPLICATE, will cause SQL error

-- Line 82-85: fk_requests_user declared TWICE
CONSTRAINT fk_requests_user FOREIGN KEY (user_id) ...
CONSTRAINT fk_requests_user FOREIGN KEY (user_id) ...  -- ← DUPLICATE name, SQL error
```
**Impact:** Schema cannot be imported as-is. Any fresh install will fail.

#### 2. `actions/create_request.php` — Wrong type in `bind_param`
```php
// Line 72: status is a string, not int — 'iii' should be 'iis'
$stmt->bind_param('iii', $user_id, $area_id, $status);
```
**Impact:** `status` gets cast to `0` silently; inserts may fail or produce wrong data.

#### 3. `actions/edit_request.php` — Space in `bind_param` format string
```php
// Line 81: 'isis i' has a space which is invalid
$stmt->bind_param('isis i', $area_id, $status, $request_id, $user_id, $admin_officer);
```
**Impact:** `bind_param` will throw a warning/error and the update will fail.

#### 4. `actions/edit_request.php` — Logic bug in permission check
```php
// Line 73: Checks if the string 'admin'/'officer' is IN the column, not the role variable
WHERE id = ? AND (user_id = ? OR ? IN ('admin', 'officer'))
// Line 80: admin_officer is set to 0/1 (int) but the SQL compares against string literals
$admin_officer = ($role === 'admin' || $role === 'officer') ? 1 : 0;
```
**Impact:** The SQL `? IN ('admin', 'officer')` will compare the integer `1` against strings, which always evaluates to false. Admins/officers cannot edit any request.

---

### 🟡 Medium

#### 5. `resident.php` — Duplicate `#requests-table` element
```html
<!-- Table appears TWICE (lines 56–71 and lines 94–109) -->
<table id="requests-table" ...>  <!-- first instance -->
...
<table id="requests-table" ...>  <!-- duplicate — same ID! -->
```
**Impact:** `querySelector('#requests-table tbody')` in `script.js` will only target the first table. The second table is dead HTML and causes invalid markup (duplicate IDs).

#### 6. `resident.php` — Two "Refresh My Requests" buttons
```html
<button id="load-table" ...>Refresh My Requests</button>   <!-- line 51 -->
...
<button id="load-requests" ...>Refresh Requests</button>   <!-- line 55 -->
```
`script.js` only wires up `#load-table`. The `#load-requests` button does nothing (the event listener assignment on line 182 uses `?.onclick =` which is invalid assignment syntax — should be `.addEventListener`).

#### 7. `js/script.js` — Invalid event listener assignment syntax
```js
// Line 182 — assignment to optional chaining is a syntax error
document.getElementById('load-reports')?.onclick = loadReports;
// Should be:
document.getElementById('load-reports')?.addEventListener('click', loadReports);
```

#### 8. `js/script.js` — `editRow()` passes wrong argument
```js
// Line 104 / 118: passes item.area (undefined) instead of item.area_name
editRow(item.id, item.area, item.status)
// The data returned by fetch_requests.php has area_name, not area
```
**Impact:** The area name is `undefined` in the edit modal — cosmetic but confusing.

#### 9. `actions/edit_request.php` — Only residents are redirected on errors
```php
// All redirects go to '../resident.php?error=...'
// Even if an admin triggers the error, they'd land on resident.php
```

#### 10. `officer.php` — Shows edit modal but officer is read-only in `script.js`
```html
<!-- officer.php includes a full edit modal (lines 38-55) -->
<!-- but script.js renders 'Read Only' text for officers -->
```
Dead markup — the officer modal is never opened.

---

### 🟢 Minor / Style

#### 11. `css/style.css` — Mixed line endings (CRLF + LF)
The file mixes Windows (`\r\n`) and Unix (`\n`) line endings. Some editors may render inconsistently.

#### 12. `admin.php` — Loads `config/db.php` but never uses `$conn`
```php
include 'config/db.php'; // line 19 — unused on this page
```

#### 13. `actions/register_user.php` — Sends `Content-Type: application/json` header then redirects
```php
header('Content-Type: application/json'); // line 5
// But all error/success paths use header('Location: ...')
// Redirects with a JSON content-type are harmless but confusing
```

#### 14. `includes/auth.php` — `session_start()` called again in many action files
Both `auth.php` and every `actions/*.php` call `session_start()`. The `header.php` guards with `if (session_status() === PHP_SESSION_NONE)`, but `auth.php` does not — this will throw a warning if included after session is already started.

#### 15. Status `'in-progress'` missing from `update_status.php` allowed list
```php
$allowed_statuses = ['pending', 'assigned', 'completed', 'cancelled'];
// 'in-progress' is a valid ENUM value in the schema but is excluded here
```

---

## Summary of Fixes Needed

| # | File | Severity | Issue |
|---|------|----------|-------|
| 1 | `database/schema.sql` | 🔴 Critical | Duplicate `truck_id` column + duplicate FK constraint name |
| 2 | `actions/create_request.php` | 🔴 Critical | `bind_param` type string `'iii'` → should be `'iis'` |
| 3 | `actions/edit_request.php` | 🔴 Critical | Space in `bind_param` format string `'isis i'` → `'isisi'` |
| 4 | `actions/edit_request.php` | 🔴 Critical | Admin/officer permission SQL logic is broken |
| 5 | `resident.php` | 🟡 Medium | Duplicate `#requests-table` element |
| 6 | `resident.php` | 🟡 Medium | Dead `#load-requests` button |
| 7 | `js/script.js` | 🟡 Medium | `?.onclick =` invalid syntax (line 182) |
| 8 | `js/script.js` | 🟡 Medium | `editRow` passes `item.area` (undefined) instead of `item.area_name` |
| 9 | `actions/edit_request.php` | 🟡 Medium | Error redirects hardcoded to `resident.php` |
| 10 | `officer.php` | 🟢 Minor | Dead edit modal markup |
| 11 | `css/style.css` | 🟢 Minor | Mixed line endings |
| 12 | `admin.php` | 🟢 Minor | Unused `config/db.php` include |
| 13 | `actions/register_user.php` | 🟢 Minor | Mismatched Content-Type + redirect |
| 14 | `includes/auth.php` | 🟢 Minor | `session_start()` not guarded |
| 15 | `actions/update_status.php` | 🟢 Minor | `'in-progress'` missing from allowed statuses |
