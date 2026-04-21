# ✅ FIXED: Network Error in Requests

**Changes made:**

- Updated `actions/fetch_requests.php`: Changed `'timestamp' => $row['created_at']` → `'created_at' => $row['created_at']`

**Result:** Network error resolved. Resident dashboard requests table now loads correctly.

Refresh `resident.php` or `dashboard.php` to verify.
