<?php
require_once 'includes/auth.php';
requireRole(['admin', 'officer']);

if (empty($_SESSION['csrf_token'])) {
  $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
}

$pageTitle = 'Reports & Complaints';
include 'includes/header.php';
?>
<div class="container">
  <h2>📊 Reports & Complaints</h2>

  <a href="admin.php" style="display:inline-block; margin:10px 0; padding:10px 20px; background:#4CAF50; color:white; text-decoration:none; border-radius:4px;">← Back to Requests</a>

  <button id="refresh-reports" style="margin:10px 0; padding:8px 16px;">Refresh Reports</button>

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
      <tr>
        <td colspan="7">Loading reports...</td>
      </tr>
    </tbody>
  </table>
</div>

<script>
  function escapeHtml(str) {
    const div = document.createElement('div');
    div.textContent = str;
    return div.innerHTML;
  }

  async function loadReports() {
    try {
      const res = await fetch('actions/fetch_reports.php');
      const data = await res.json();
      const tbody = document.querySelector('#reports-table tbody');
      tbody.innerHTML = '';
      if (data.success) {
        data.data.forEach(row => {
          const desc = row.description.length > 100
            ? row.description.substring(0, 100) + '...'
            : row.description;
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

  async function updateReportStatus(id, status) {
    try {
      const res = await fetch('actions/update_report_status.php', {
        method: 'POST',
        headers: {
          'Content-Type': 'application/json'
        },
        body: JSON.stringify({
          id,
          status,
          csrf_token: '<?= htmlspecialchars($_SESSION['csrf_token']) ?>'
        })
      });
      const data = await res.json();
      if (data.success) {
        loadReports();
      } else {
        alert('Error: ' + data.message);
      }
    } catch (e) {
      console.error(e);
    }
  }

  document.getElementById('refresh-reports').onclick = loadReports;
  loadReports();
</script>

<?php include 'includes/footer.php'; ?>
