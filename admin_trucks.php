<?php

/**
 * Truck Management Page
 *
 * Purpose:
 * Allows administrators to manage the fleet of garbage collection trucks.
 * Every truck has a name/identifier (e.g., "Truck-001") and a status that
 * reflects its current operational state: available, busy, or maintenance.
 *
 * Business Context:
 * The Garbage Tracker system assigns trucks to collection schedules and routes.
 * Keeping an up-to-date truck inventory helps the admin ensure that enough
 * vehicles are available for planned pickups and that vehicles under repair are
 * marked accordingly so they aren't assigned to new jobs.
 *
 * Workflow:
 * - Only users with the 'admin' role can access this page.
 * - The page displays a table of all trucks with their current status.
 * - The admin can add a new truck via the form (name + default "available" status).
 * - Each row contains a dropdown to change the truck’s status instantly
 *   (AJAX update without page reload).
 * - The table is refreshed via a "Refresh List" button or automatically after
 *   adding a truck or changing a status.
 *
 * Security:
 * - CSRF token is embedded in the add-truck form and sent with status updates.
 * - All dynamic content inserted into the DOM is escaped using escapeHtml() to
 *   prevent XSS.
 * - The backend actions (fetch_trucks.php, create_truck.php, update_truck_status.php)
 *   must enforce the admin role and validate the CSRF token.
 */
require_once 'includes/auth.php';

// Restrict access to administrators only
requireRole(['admin']);

// Generate a per-session CSRF token if one does not already exist.
// This token protects all state-changing requests from cross-site request forgery.
if (empty($_SESSION['csrf_token'])) {
  $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
}

$pageTitle = 'Truck Management';
include 'includes/header.php';
?>
<div class="container">
  <h2>🚚 Truck Management</h2>

  <!-- Navigation back to the main admin dashboard -->
  <a href="admin.php" style="display:inline-block; margin:10px 0; padding:10px 20px; background:#4CAF50; color:white; text-decoration:none; border-radius:4px;">← Back to Dashboard</a>

  <!-- Form to register a new garbage truck into the system -->
  <form id="truck-form" method="POST" style="margin:20px 0;">
    <!-- Hidden CSRF token to validate the request server-side -->
    <input type="hidden" name="csrf_token" value="<?= htmlspecialchars($_SESSION['csrf_token']) ?>">
    <!-- Truck name field: descriptive identifier, e.g. "Truck-001" -->
    <input type="text" id="truck-name" name="name" placeholder="Truck Name/Number (e.g. Truck-001)" required style="padding:10px; width:300px; margin-right:10px;">
    <button type="submit" style="padding:10px 20px; background:#FF9800; color:white; border:none; cursor:pointer;">Add Truck</button>
  </form>

  <!-- Manual refresh button to reload the truck list without a full page reload -->
  <button id="refresh-trucks" style="margin:10px 0; padding:8px 16px;">Refresh List</button>

  <!-- Table dynamically filled with truck data -->
  <table id="trucks-table" border="1" style="width:100%; border-collapse:collapse;">
    <thead>
      <tr style="background:#f2f2f2;">
        <th>ID</th>
        <th>Name</th>
        <th>Status</th>
        <th>Created</th>
        <th>Actions</th>
      </tr>
    </thead>
    <tbody>
      <!-- Placeholder while data is being fetched via AJAX -->
      <tr>
        <td colspan="5">Loading trucks...</td>
      </tr>
    </tbody>
  </table>
</div>

<script>
  /**
   * Escape a string to prevent XSS when injecting user‑generated content into HTML.
   * Uses the browser's built‑in textContent property which automatically encodes
   * special characters, then reads back the encoded HTML via innerHTML.
   *
   * @param {string} str - Untrusted string that may contain HTML metacharacters.
   * @returns {string} HTML‑escaped safe string.
   */
  function escapeHtml(str) {
    const div = document.createElement('div');
    div.textContent = str; // Set as plain text – any HTML will be escaped
    return div.innerHTML; // Retrieve the escaped representation
  }

  /**
   * Fetch the current list of trucks from the server and render them into the table.
   * Called on page load, after adding a truck, and when the "Refresh List" button is clicked.
   *
   * The backend endpoint (actions/fetch_trucks.php) returns a JSON object:
   *   { success: true, data: [ { id, name, status, created_at }, ... ] }
   *
   * Each row displays:
   * - Truck ID (parsed as integer for safety)
   * - Truck name (escaped)
   * - Status displayed with colour coding (green/available, orange/busy, red/maintenance)
   * - Creation timestamp (escaped)
   * - A dropdown to change the truck’s operational status
   */
  async function loadTrucks() {
    try {
      const res = await fetch('actions/fetch_trucks.php');
      const data = await res.json();
      const tbody = document.querySelector('#trucks-table tbody');
      tbody.innerHTML = ''; // Clear existing rows
      if (data.success) {
        data.data.forEach(row => {
          // Determine the colour for the status badge based on operational state
          const statusColor = row.status === 'available' ? 'green' : row.status === 'busy' ? 'orange' : 'red';
          // Build the table row using template literals; all dynamic values are escaped
          tbody.innerHTML += `
            <tr>
              <td>${parseInt(row.id)}</td>
              <td>${escapeHtml(row.name)}</td>
              <td><span style="color:${statusColor}">${escapeHtml(row.status)}</span></td>
              <td>${escapeHtml(row.created_at)}</td>
              <td>
                <select onchange="updateStatus(${parseInt(row.id)}, this.value)">
                  <option value="available" ${row.status==='available'?'selected':''}>Available</option>
                  <option value="busy" ${row.status==='busy'?'selected':''}>Busy</option>
                  <option value="maintenance" ${row.status==='maintenance'?'selected':''}>Maintenance</option>
                </select>
              </td>
            </tr>
          `;
        });
      }
    } catch (e) {
      console.error('Load error:', e); // Log fetch or parsing errors – the table remains in its last state
    }
  }

  /**
   * Handle the submission of the "Add Truck" form.
   * Prevents the default page reload, gathers form data (including the CSRF token)
   * and sends it via AJAX to actions/create_truck.php.
   * On success the table is refreshed and the form fields are cleared.
   */
  document.getElementById('truck-form').addEventListener('submit', async (e) => {
    e.preventDefault(); // Stop browser from submitting normally
    const formData = new FormData(e.target); // Collect all form fields, including the hidden CSRF token
    try {
      const res = await fetch('actions/create_truck.php', {
        method: 'POST',
        body: formData
      });
      const data = await res.json();
      if (data.success) {
        e.target.reset(); // Clear the input field for the next entry
        alert('Truck added!'); // Notify the admin
        loadTrucks(); // Update the table immediately
      } else {
        alert('Error: ' + data.message);
      }
    } catch (e) {
      alert('Network error'); // Generic network failure alert
    }
  });

  /**
   * Update a truck’s operational status via AJAX.
   *
   * Sends the truck ID, new status, and CSRF token (read from the hidden form field)
   * to actions/update_truck_status.php as a JSON payload.
   * On success the table is reloaded to reflect the change.
   *
   * @param {number} id - The truck's database ID.
   * @param {string} status - The new status: 'available', 'busy', or 'maintenance'.
   */
  async function updateStatus(id, status) {
    try {
      const res = await fetch('actions/update_truck_status.php', {
        method: 'POST',
        headers: {
          'Content-Type': 'application/json'
        },
        body: JSON.stringify({
          id,
          status,
          // Retrieve the CSRF token from the hidden input that was rendered with the page
          csrf_token: document.querySelector('input[name="csrf_token"]').value
        })
      });
      const data = await res.json();
      if (data.success) {
        loadTrucks(); // Refresh the list to show the updated status
      }
    } catch (e) {
      console.error(e);
    }
  }

  // Attach the refresh button handler and load the truck list when the page is ready
  document.getElementById('refresh-trucks').onclick = loadTrucks;
  loadTrucks();
</script>

<?php include 'includes/footer.php'; ?>
