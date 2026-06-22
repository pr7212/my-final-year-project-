# Hosting Garbage Tracker Online

This app is plain **PHP + MySQL** — it works on free hosts (InfinityFree, 000webhost, etc.) and paid shared hosting.

## Recommended: InfinityFree / ByetCluster (free)

You already had this type of account (`sql100.byetcluster.com`). Steps:

### 1. Create account & site

1. Sign up at [InfinityFree](https://infinityfree.com) (or your provider).
2. Add a website and note your **domain** (e.g. `yoursite.infinityfreeapp.com`).

### 2. Create MySQL database

In the hosting control panel → **MySQL Databases**:

| Field | Example |
|-------|---------|
| Host | `sql100.byetcluster.com` |
| Username | `if0_XXXXXXX` |
| Password | *(set in panel)* |
| Database name | `if0_XXXXXXX_gmts` |

Copy these four values — you need them for `.env`.

### 3. Import database schema

1. Open **phpMyAdmin** from the control panel.
2. Select your database.
3. **Import** → choose `database/schema.sql` from this project.
4. Confirm tables were created (`users`, `requests`, `trucks`, etc.).

> Do **not** run `setup_db.php` on the web — it is CLI-only and blocked by `.htaccess`.

### 4. Upload project files

Upload **everything inside** the `garbage-tracker` folder to **`htdocs`** (document root), not the parent folder.

Your live URLs should look like:

- `https://yoursite.infinityfreeapp.com/index.php`
- `https://yoursite.infinityfreeapp.com/register.php`

**Upload checklist:**

- `index.php`, `register.php`, `dashboard.php`, role pages (`admin.php`, `resident.php`, …)
- `actions/`, `config/`, `css/`, `js/`, `includes/`
- `.htaccess`
- **Do not upload** `.env` from your PC (local credentials). Create a new one on the server.

### 5. Create `.env` on the server

In `htdocs`, create a file named `.env`:

```env
DB_HOST=sql100.byetcluster.com
DB_USER=if0_XXXXXXX
DB_PASS=your_password_from_panel
DB_NAME=if0_XXXXXXX_gmts
```

Use the exact values from step 2. No quotes around values.

### 6. Test

1. Open `https://your-domain/index.php`
2. Register a user → login → open your role dashboard.

If you see *"database connection error"*:

- Host must be the **MySQL hostname** from the panel (not your website URL).
- Database name and username often share the same prefix (`if0_...`).
- Wait a few minutes after creating the DB (some free hosts are slow).
- Confirm `database/schema.sql` was imported.

---

## Switch between local and hosted

| Environment | `.env` |
|-------------|--------|
| XAMPP local | `DB_HOST=localhost`, `DB_USER=root`, `DB_PASS=` empty, `DB_NAME=garbage_tracker` |
| Online | Host, user, pass, name from hosting panel |

Keep **two copies**: local `.env` on your PC, production `.env` only on the server (via File Manager or FTP).

---

## Other hosts (paid shared / VPS)

1. PHP **8.0+** with `mysqli` enabled.
2. MySQL 5.7+ or MariaDB.
3. Point the site root to the `garbage-tracker` folder (or upload contents to `public_html`).
4. Import `database/schema.sql`.
5. Add `.env` with production DB credentials.

---

## Security reminders

- Never commit `.env` to GitHub (it is in `.gitignore`).
- Use HTTPS when your host provides free SSL (InfinityFree: SSL tab in panel).
- Change default passwords after first login if you seed admin users in SQL.
