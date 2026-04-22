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
    <div class="alert alert-success">
      Request submitted successfully.
    </div>
  <?php elseif ($error !== ''): ?>
    <div class="alert alert-error">
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
    <input type="text" id="report-location" name="location" placeholder="Problem Location (e.g. Street corner)" required>
    <textarea id="report-description" name="description" placeholder="Describe the issue (overflowing bins, bad smell, etc.)" required></textarea>
    <button type="submit" class="btn">Submit Report</button>
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
    <h3>📅 Collection Schedule</h3>
    <button id="load-schedules" type="button" class="btn mt-4 mb-4">Refresh Schedule</button>
    <div class="table-responsive">
      <table id="schedules-table">
        <thead>
          <tr>
            <th>Date</th>
            <th>Location</th>
            <th>Status</th>
          </tr>
        </thead>
        <tbody>
          <tr>
            <td colspan="3">Loading...</td>
          </tr>
        </tbody>
      </table>
    </div>
  </div>

  <div style="margin:20px 0;">
    <h3>📋 My Pickup Requests</h3>
    <button id="load-table" type="button" class="btn mt-4 mb-4">Refresh Requests</button>
    <div class="table-responsive">
      <table id="requests-table">
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
  </div>

  <div style="margin:20px 0;">
    <h3>📊 My Reports</h3>
    <button id="load-reports" type="button" class="btn mt-4 mb-4">Refresh Reports</button>
    <div class="table-responsive">
      <table id="reports-table">
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
  </div>

  <dialog id="edit-modal">
    <h4>Edit Request</h4>
    <input type="hidden" id="edit-id">
    <label>Area: <select id="edit-area_id" required>
        <option value="">Select Area</option>
      </select></label><br><br>
    <label>Status:
      <select id="edit-status">
        <option value="pending">Pending</option>
        <option value="cancelled">Cancelled</option>
      </select>
    </label><br><br>
    <button type="button" class="btn" onclick="saveEdit()">Save</button>
    <button type="button" class="btn btn-danger" onclick="closeModal()">Cancel</button>
  </dialog>
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
              <td><span class="status-badge status-${row.status.toLowerCase()}">${row.status}</span></td>
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

  async function loadSchedules() {
    try {
      const res = await fetch('actions/fetch_schedules.php');
      const data = await res.json();
      const tbody = document.querySelector('#schedules-table tbody');
      tbody.innerHTML = '';
      if (data.success && data.data.length) {
        data.data.forEach(row => {
          tbody.innerHTML += `
            <tr>
              <td>${row.collection_date}</td>
              <td>${row.location}</td>
              <td><span class="status-badge status-${row.status.toLowerCase()}">${row.status}</span></td>
            </tr>
          `;
        });
      } else {
        tbody.innerHTML = '<tr><td colspan="3">No schedules found</td></tr>';
      }
    } catch (e) {
      console.error(e);
    }
  }

  document.getElementById('load-schedules')?.addEventListener('click', loadSchedules);
  loadSchedules();
</script>
<script src="js/script.js"></script>
<?php include 'includes/footer.php'; ?>
