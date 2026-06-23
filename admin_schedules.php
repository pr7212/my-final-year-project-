<?php

/**
 * Schedule Management Page
 *
 * Allows admin users to add collection schedules and view existing ones.
 * Includes CSRF protection and dynamic table loading via AJAX.
 */
require_once 'includes/auth.php';
requireRole(['admin']);

// Generate a CSRF token for the session if one does not exist
if (empty($_SESSION['csrf_token'])) {
  $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
}

$pageTitle = 'Schedule Management';
include 'includes/header.php';
?>
<div class="container">
  <h2>🗓️ Schedule Management</h2>

  <!-- Form to add a new schedule entry -->
  <form id="schedule-form" method="POST">
    <!-- Hidden CSRF token field to prevent cross-site request forgery -->
    <input type="hidden" name="csrf_token" value="<?= htmlspecialchars($_SESSION['csrf_token']) ?>">
    <div style="margin: 10px 0;">
      <input type="text" id="location" name="location" placeholder="Collection Location" required style="padding:8px; width:300px;">
      <input type="date" id="collection_date" name="collection_date" required style="padding:8px;">
      <button type="submit" style="padding:8px 16px; background:#4CAF50; color:white; border:none; cursor:pointer;">Add Schedule</button>
    </div>
  </form>

  <!-- Button to manually refresh the schedules table -->
  <button id="refresh-schedules" style="margin:10px 0; padding:8px 16px;">Refresh List</button>

  <!-- Table dynamically filled with schedule data -->
  <table id="schedules-table" border="1" style="width:100%; border-collapse:collapse;">
    <thead>
      <tr style="background:#f2f2f2;">
        <th>ID</th>
        <th>Location</th>
        <th>Date</th>
        <th>Status</th>
        <th>Created</th>
      </tr>
    </thead>
    <tbody>
      <!-- Placeholder shown while schedules are being loaded -->
      <tr>
        <td colspan="5">Loading schedules...</td>
      </tr>
    </tbody>
  </table>
</div>

<script>
  /**
   * Escape a string to prevent XSS when injecting into HTML.
   * @param {string} str - The untrusted string to escape.
   * @returns {string} HTML-escaped version.
   */
  function escapeHtml(str) {
    const div = document.createElement('div');
    div.textContent = str;
    return div.innerHTML;
  }

  /**
   * Fetch and display all schedules from the server.
   * Replaces the tbody content with table rows built from the JSON response.
   */
  async function loadSchedules() {
    try {
      const res = await fetch('actions/fetch_schedules.php');
      const data = await res.json();
      const tbody = document.querySelector('#schedules-table tbody');
      tbody.innerHTML = '';
      if (data.success) {
        data.data.forEach(row => {
          // Build a row with escaped values; status colour-coded
          tbody.innerHTML += `
            <tr>
              <td>${parseInt(row.id)}</td>
              <td>${escapeHtml(row.location)}</td>
              <td>${escapeHtml(row.collection_date)}</td>
              <td><span style="color:${row.status === 'completed' ? 'green' : 'orange'}">${escapeHtml(row.status)}</span></td>
              <td>${escapeHtml(row.created_at)}</td>
            </tr>
          `;
        });
      }
    } catch (e) {
      console.error('Load error:', e);
    }
  }

  // Attach submit handler to the schedule creation form
  document.getElementById('schedule-form').addEventListener('submit', async (e) => {
    e.preventDefault(); // Stop default form submission (page reload)
    const formData = new FormData(e.target);
    try {
      // Send the form data (including CSRF token) to the creation endpoint
      const res = await fetch('actions/create_schedule.php', {
        method: 'POST',
        body: formData
      });
      const data = await res.json();
      if (data.success) {
        loadSchedules(); // Refresh the schedule list
        e.target.reset(); // Clear the form fields
        alert('Schedule added!');
      } else {
        alert('Error: ' + data.message);
      }
    } catch (e) {
      alert('Network error');
    }
  });

  // Set up the refresh button and load schedules on page load
  document.getElementById('refresh-schedules').onclick = loadSchedules;
  loadSchedules();
</script>

<?php include 'includes/footer.php'; ?>
