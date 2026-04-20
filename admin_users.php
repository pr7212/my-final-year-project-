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
  <title>Admin - Users</title>
  <link rel="stylesheet" href="css/style.css">
</head>

<body>
  <?php include 'includes/header.php'; ?>

  <div class="container">
    <h2>👥 User Management</h2>

    <a href="admin.php" style="display:inline-block; margin:10px 0; padding:10px 20px; background:#4CAF50; color:white; text-decoration:none; border-radius:4px;">← Back to Dashboard</a>

    <form id="user-form" method="POST" style="margin:20px 0; padding:20px; background:#f9f9f9; border-radius:8px;">
      <input type="hidden" name="csrf_token" value="<?= htmlspecialchars($_SESSION['csrf_token']) ?>">
      <div style="display:flex; gap:10px; margin-bottom:10px;">
        <input type="text" name="name" placeholder="Full Name" required style="flex:1; padding:10px;">
        <input type="email" name="email" placeholder="Email" required style="flex:1; padding:10px;">
        <input type="password" name="password" placeholder="Password" required style="flex:1; padding:10px;">
      </div>
      <select name="role" required style="padding:10px; margin-bottom:10px; width:100%;">
        <option value="">Select Role</option>
        <option value="resident">Resident</option>
        <option value="collector">Collector</option>
        <option value="officer">Officer</option>
        <option value="admin">Admin (careful!)</option>
      </select>
      <button type="submit" style="padding:12px 24px; background:#9C27B0; color:white; border:none; border-radius:4px; cursor:pointer;">Create User</button>
    </form>

    <button id="refresh-users" style="margin:10px 0; padding:8px 16px;">Refresh Users</button>

    <table id="users-table" border="1" style="width:100%; border-collapse:collapse;">
      <thead>
        <tr style="background:#f2f2f2;">
          <th>ID</th>
          <th>Name</th>
          <th>Email</th>
          <th>Role</th>
          <th>Created</th>
        </tr>
      </thead>
      <tbody>
        <tr>
          <td colspan="5">Loading users...</td>
        </tr>
      </tbody>
    </table>
  </div>

  <script>
    async function loadUsers() {
      try {
        const res = await fetch('actions/fetch_users.php');
        const data = await res.json();
        const tbody = document.querySelector('#users-table tbody');
        tbody.innerHTML = '';
        if (data.success) {
          data.data.forEach(row => {
            tbody.innerHTML += `
              <tr>
                <td>${row.id}</td>
                <td>${row.name}</td>
                <td>${row.email}</td>
                <td><span style="background:#e3f2fd; padding:4px 8px; border-radius:12px; font-size:0.9em;">${row.role}</span></td>
                <td>${row.created_at}</td>
              </tr>
            `;
          });
        }
      } catch (e) {
        console.error('Load error:', e);
      }
    }

    document.getElementById('user-form').addEventListener('submit', async (e) => {
      e.preventDefault();
      const formData = new FormData(e.target);
      try {
        const res = await fetch('actions/create_user.php', {
          method: 'POST',
          body: formData
        });
        const data = await res.json();
        if (data.success) {
          e.target.reset();
          alert(`User ${data.message}`);
          loadUsers();
        } else {
          alert('Error: ' + data.message);
        }
      } catch (e) {
        alert('Network error');
      }
    });

    document.getElementById('refresh-users').onclick = loadUsers;
    loadUsers();
  </script>

  <?php include 'includes/footer.php'; ?>
</body>

</html>
