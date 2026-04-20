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
  <title>Admin - Trucks</title>
  <link rel="stylesheet" href="css/style.css">
</head>

<body>
  <?php include 'includes/header.php'; ?>

  <div class="container">
    <h2>🚚 Truck Management</h2>

    <a href="admin.php" style="display:inline-block; margin:10px 0; padding:10px 20px; background:#4CAF50; color:white; text-decoration:none; border-radius:4px;">← Back to Dashboard</a>

    <form id="truck-form" method="POST" style="margin:20px 0;">
      <input type="hidden" name="csrf_token" value="<?= htmlspecialchars($_SESSION['csrf_token']) ?>">
      <input type="text" id="truck-name" name="name" placeholder="Truck Name/Number (e.g. Truck-001)" required style="padding:10px; width:300px; margin-right:10px;">
      <button type="submit" style="padding:10px 20px; background:#FF9800; color:white; border:none; cursor:pointer;">Add Truck</button>
    </form>

    <button id="refresh-trucks" style="margin:10px 0; padding:8px 16px;">Refresh List</button>

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
        <tr>
          <td colspan="5">Loading trucks...</td>
        </tr>
      </tbody>
    </table>
  </div>

  <script>
    async function loadTrucks() {
      try {
        const res = await fetch('actions/fetch_trucks.php');
        const data = await res.json();
        const tbody = document.querySelector('#trucks-table tbody');
        tbody.innerHTML = '';
        if (data.success) {
          data.data.forEach(row => {
            const statusColor = row.status === 'available' ? 'green' : row.status === 'busy' ? 'orange' : 'red';
            tbody.innerHTML += `
              <tr>
                <td>${row.id}</td>
                <td>${row.name}</td>
                <td><span style="color:${statusColor}">${row.status}</span></td>
                <td>${row.created_at}</td>
                <td>
                  <select onchange="updateStatus(${row.id}, this.value)">
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
        console.error('Load error:', e);
      }
    }

    document.getElementById('truck-form').addEventListener('submit', async (e) => {
      e.preventDefault();
      const formData = new FormData(e.target);
      try {
        const res = await fetch('actions/create_truck.php', {
          method: 'POST',
          body: formData
        });
        const data = await res.json();
        if (data.success) {
          e.target.reset();
          alert('Truck added!');
          loadTrucks();
        } else {
          alert('Error: ' + data.message);
        }
      } catch (e) {
        alert('Network error');
      }
    });

    async function updateStatus(id, status) {
      try {
        const res = await fetch('actions/update_truck_status.php', {
          method: 'POST',
          headers: {
            'Content-Type': 'application/json'
          },
          body: JSON.stringify({
            id,
            status
          })
        });
        const data = await res.json();
        if (data.success) {
          loadTrucks();
        }
      } catch (e) {
        console.error(e);
      }
    }

    document.getElementById('refresh-trucks').onclick = loadTrucks;
    loadTrucks();
  </script>

  <?php include 'includes/footer.php'; ?>
</body>

</html>
