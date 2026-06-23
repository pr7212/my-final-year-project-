<?php

/**
 * Reports & Complaints Page
 *
 * Displays a table of submitted reports/complaints for admin/officer roles.
 * Allows refreshing the list and updating report status via AJAX.
 * Requires admin or officer authentication.
 */

// Include authentication helper and enforce role-based access
require_once 'includes/auth.php';
requireRole(['admin', 'officer']);

// Generate a CSRF token if one does not already exist in the session
if (empty($_SESSION['csrf_token'])) {
  $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
}

// Set the page title for the header template
$pageTitle = 'Reports & Complaints';
include 'includes/header.php';
?>
<!-- Main container for the reports interface -->
<div class="container">
  <h2>📊 Reports & Complaints</h2>

  <!-- Navigation link back to the admin requests panel -->
  <a href="admin.php" style="display:inline-block; margin:10px 0; padding:10px 20px; background:#4CAF50; color:white; text-decoration:none; border-radius:4px;">← Back to Requests</a>

  <!-- Button to manually refresh the reports table via JavaScript -->
  <button id="refresh-reports" style="margin:10px 0; padding:8px 16px;">Refresh Reports</button>

  <!-- Table that will be dynamically populated with report data -->
  <table id="reports-table" border="1" style="width:100%; border-collapse:collapse;">
    <thead>
      <tr style="background:#f2f2f2;">
        <th>ID</th>
        <th>User</th>
        <th>Location</th>
        <th>Description</th>
        <th>Status</th>
        <th>Reported</th>
        <th>Actions</th>
      </tr>
    </thead>
    <tbody>
      <!-- Placeholder row shown while reports are being loaded -->
      <tr>
        <td colspan="7">Loading reports...</td>
      </tr>
    </tbody>
  </table>
</div>

<script>
  /**
   * Escape a string to prevent XSS when inserting into HTML.
   * Uses the browser's built-in textContent setter and innerHTML getter.
   * @param {string} str - The untrusted string to escape.
   * @returns {string} The HTML‑escaped string.
   */
  function escapeHtml(str) {
    const div = document.createElement('div');
    div.textContent = str;
    return div.innerHTML;
  }

  /**
   * Fetch the list of reports from the server and render them into the table.
   * Replaces the entire tbody content with rows based on the JSON response.
   */
  async function loadReports() {
    try {
      // Fetch reports from the dedicated endpoint
      const res = await fetch('actions/fetch_reports.php');
      const data = await res.json();
      const tbody = document.querySelector('#reports-table tbody');
      tbody.innerHTML = '';
      if (data.success) {
        data.data.forEach(row => {
          // Truncate long descriptions for display
          const desc = row.description.length > 100 ?
            row.description.substring(0, 100) + '...' :
            row.description;
          // Build a table row with escaped values and a status dropdown
          tbody.innerHTML += `
            <tr>
              <td>${parseInt(row.id)}</td>
              <td>${escapeHtml(row.user_name)} (ID:${parseInt(row.user_id)})</td>
              <td>${escapeHtml(row.location)}</td>
              <td>${escapeHtml(desc)}</td>
              <td><span style="color:${row.status === 'resolved' ? 'green' : 'orange'}">${escapeHtml(row.status)}</span></td>
              <td>${escapeHtml(new Date(row.created_at).toLocaleString())}</td>
              <td>
                <select onchange="updateReportStatus(${parseInt(row.id)}, this.value)">
                  <option value="pending" ${row.status==='pending'?'selected':''}>Pending</option>
                  <option value="resolved" ${row.status==='resolved'?'selected':''}>Resolved</option>
                </select>
              </td>
            </tr>
          `;
        });
      }
    } catch (e) {
      console.error('Load error:', e);
    }
  }

  /**
   * Send an AJAX request to change the status of a report.
   * On success the table is reloaded; on failure an alert is shown.
   * @param {number} id - The report ID.
   * @param {string} status - The new status ("pending" or "resolved").
   */
  async function updateReportStatus(id, status) {
    try {
      const res = await fetch('actions/update_report_status.php', {
        method: 'POST',
        headers: {
          'Content-Type': 'application/json'
        },
        // Include the CSRF token from the session (injected by PHP)
        body: JSON.stringify({
          id,
          status,
          csrf_token: '<?= htmlspecialchars($_SESSION['csrf_token']) ?>'
        })
      });
      const data = await res.json();
      if (data.success) {
        // Refresh the list to reflect the change
        loadReports();
      } else {
        alert('Error: ' + data.message);
      }
    } catch (e) {
      console.error(e);
    }
  }

  // Attach the refresh button click handler and load reports on page load
  document.getElementById('refresh-reports').onclick = loadReports;
  loadReports();
</script>

<?php include 'includes/footer.php'; ?>
