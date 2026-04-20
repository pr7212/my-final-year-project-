<?php
require_once 'includes/auth.php';
requireRole(['admin']);

if (empty($_SESSION['csrf_token'])) {
  $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
}
?>
<!DOCTYPE html>
<html>

<head>
  <title>Admin - Schedules</title>
  <link rel="stylesheet" href="css/style.css">
</head>

<body>
  <?php include 'includes/header.php'; ?>

  <div class="container">
    <h2>🗓️ Schedule Management</h2>

    <form id="schedule-form" method="POST">
      <input type="hidden" name="csrf_token" value="<?= htmlspecialchars($_SESSION['csrf_token']) ?>">
      <div style="margin: 10px 0;">
        <input type="text" id="location" name="location" placeholder="Collection Location" required style="padding:8px; width:300px;">
        <input type="date" id="collection_date" name="collection_date" required style="padding:8px;">
        <button type="submit" style="padding:8px 16px; background:#4CAF50; color:white; border:none; cursor:pointer;">Add Schedule</button>
      </div>
    </form>

    <button id="refresh-schedules" style="margin:10px 0; padding:8px 16px;">Refresh List</button>

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
        <tr>
          <td colspan="5">Loading schedules...</td>
        </tr>
      </tbody>
    </table>
  </div>

  <script>
    async function loadSchedules() {
      try {
        const res = await fetch('actions/fetch_schedules.php');
        const data = await res.json();
        const tbody = document.querySelector('#schedules-table tbody');
        tbody.innerHTML = '';
        if (data.success) {
          data.data.forEach(row => {
            tbody.innerHTML += `
              <tr>
                <td>${row.id}</td>
                <td>${row.location}</td>
                <td>${row.collection_date}</td>
                <td><span style="color:${row.status === 'completed' ? 'green' : 'orange'}">${row.status}</span></td>
                <td>${row.created_at}</td>
              </tr>
            `;
          });
        }
      } catch (e) {
        console.error('Load error:', e);
      }
    }

    document.getElementById('schedule-form').addEventListener('submit', async (e) => {
      e.preventDefault();
      const formData = new FormData(e.target);
      try {
        const res = await fetch('actions/create_schedule.php', {
          method: 'POST',
          body: formData
        });
        const data = await res.json();
        if (data.success) {
          loadSchedules();
          e.target.reset();
          alert('Schedule added!');
        } else {
          alert('Error: ' + data.message);
        }
      } catch (e) {
        alert('Network error');
      }
    });

    document.getElementById('refresh-schedules').onclick = loadSchedules;
    loadSchedules();
  </script>

  <?php include 'includes/footer.php'; ?>
</body>

</html>
