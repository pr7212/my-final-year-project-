<?php
include 'includes/auth.php';
requireRole('resident');

if (empty($_SESSION['csrf_token'])) {
  $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
}

$pageTitle = 'Resident Dashboard';
$success = $_GET['success'] ?? '';
$error = $_GET['error'] ?? '';

include 'includes/header.php';
?>
<div class="container">
  <div id="feedback" style="display:none; padding:10px; margin:10px 0;"></div>

  <?php if ($success === 'request_submitted'): ?>
    <div style="display:block; color:green; background:#fff; padding:10px; margin:10px 0;">
      Request submitted successfully.
    </div>
  <?php elseif ($error !== ''): ?>
    <div style="display:block; color:red; background:#fff; padding:10px; margin:10px 0;">
      <?= htmlspecialchars(str_replace('_', ' ', $error)) ?>
    </div>
  <?php endif; ?>

  <h2>🏠 Resident Dashboard</h2>

  <div style="margin:20px 0;">
    <h3>📋 My Requests</h3>
  </div>

  <h3>📈 Submit Report/Complaint</h3>
  <form id="report-form">
    <input type="hidden" name="csrf_token" value="<?= htmlspecialchars($_SESSION['csrf_token']) ?>">
    <input type="text" id="report-location" name="location" placeholder="Problem Location (e.g. Street corner)" required style="width:100%; padding:10px; margin:5px 0;">
    <textarea id="report-description" name="description" placeholder="Describe the issue (overflowing bins, bad smell, etc.)" required style="width:100%; padding:10px; height:100px; margin:5px 0;"></textarea>
    <button type="submit" style="padding:10px 20px; background:#F44336; color:white; border:none;">Submit Report</button>
  </form>

  <h3>🚚 Create Garbage Pickup Request</h3>
  <form id="create-form">
    <input type="hidden" name="csrf_token" value="<?= htmlspecialchars($_SESSION['csrf_token']) ?>">
    <select id="area-select" name="area_id" required>
      <option value="">Loading areas...</option>
    </select>
    <button type="submit">Submit Pickup Request</button>
  </form>


  <div style="margin:20px 0;">
    <h3>📋 My Pickup Requests</h3>
    <button id="load-table" type="button" style="margin:10px 0;">Refresh Requests</button>
    <table id="requests-table" border="1" style="width:100%;">
      <thead>
        <tr>
          <th>ID</th>
          <th>Area</th>
          <th>Truck</th>
          <th>Status</th>
          <th>Actions</th>
        </tr>
      </thead>
      <tbody>
        <tr>
          <td colspan="5">Loading...</td>
        </tr>
      </tbody>
    </table>
  </div>

  <div style="margin:20px 0;">
    <h3>📊 My Reports</h3>
    <button id="load-reports" type="button" style="margin:10px 0;">Refresh Reports</button>
    <table id="reports-table" border="1" style="width:100%;">
      <thead>
        <tr>
          <th>ID</th>
          <th>Location</th>
          <th>Status</th>
          <th>Description</th>
          <th>Reported</th>
        </tr>
      </thead>
      <tbody>
        <tr>
          <td colspan="5">Loading...</td>
        </tr>
      </tbody>
    </table>
  </div>


  <div id="edit-modal" style="display:none; position:fixed; top:20%; left:20%; background:white; border:2px solid #ccc; padding:20px; z-index:1000;">
    <h4>Edit Request</h4>
    <input type="hidden" id="edit-id">
    <label>Area: <select id="edit-area_id" required>
        <option value="">Select Area</option>
      </select></label><br><br>
    <label>Status:
      <select id="edit-status">
        <option value="pending">Pending</option>
        <option value="assigned">Assigned</option>
        <option value="in-progress">In Progress</option>
        <option value="completed">Completed</option>
        <option value="cancelled">Cancelled</option>
      </select>
    </label><br><br>
    <button type="button" onclick="saveEdit()">Save</button>
    <button type="button" onclick="closeModal()">Cancel</button>
  </div>
</div>

<script>
  document.body.dataset.role = 'resident';

  // Reports functionality
  document.getElementById('report-form').addEventListener('submit', async (e) => {
    e.preventDefault();
    const formData = new FormData(e.target);
    try {
      const res = await fetch('actions/create_report.php', {
        method: 'POST',
        body: formData
      });
      const data = await res.json();
      if (data.success) {
        e.target.reset();
        alert('Report submitted!');
        loadReports();
      } else {
        alert('Error: ' + data.message);
      }
    } catch (e) {
      alert('Network error');
    }
  });

  async function loadReports() {
    try {
      const res = await fetch('actions/fetch_reports.php');
      const data = await res.json();
      const tbody = document.querySelector('#reports-table tbody');
      tbody.innerHTML = '';
      if (data.success && data.data.length) {
        data.data.forEach(row => {
          tbody.innerHTML += `
            <tr>
              <td>${row.id}</td>
              <td>${row.location}</td>
              <td><span style="color:${row.status === 'resolved' ? 'green' : 'orange'}">${row.status}</span></td>
              <td>${row.description.substring(0,50)}${row.description.length>50?'...':''}</td>
              <td>${new Date(row.created_at).toLocaleString()}</td>
            </tr>
          `;
        });
      } else {
        tbody.innerHTML = '<tr><td colspan="5">No reports</td></tr>';
      }
    } catch (e) {
      console.error(e);
    }
  }

  document.getElementById('load-reports')?.addEventListener('click', loadReports);
</script>
<script src="js/script.js"></script>
<?php include 'includes/footer.php'; ?>
