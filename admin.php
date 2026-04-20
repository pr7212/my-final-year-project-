<?php
include 'includes/auth.php';

if ($_SESSION['role'] !== 'admin') {
  header("Location: index.php");
  exit();
}

if (empty($_SESSION['csrf_token'])) {
  $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
}

$pageTitle = 'Dashboard';
$success = $_GET['success'] ?? '';
$error = $_GET['error'] ?? '';

include 'includes/header.php';
include 'config/db.php';
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

  <h2><?= $isAdmin ? 'Manage Requests' : 'My Dashboard' ?></h2>

  <div style="margin:20px 0; padding:15px; background:#e8f4f8; border-radius:8px;">
    <strong>🏠 Admin Navigation:</strong>
    <a href="admin.php" style="margin:0 15px; padding:8px 12px; background:#4CAF50; color:white; text-decoration:none; border-radius:4px;">📋 Requests</a>
    <a href="admin_schedules.php" style="margin:0 15px; padding:8px 12px; background:#2196F3; color:white; text-decoration:none; border-radius:4px;">📅 Schedules</a>
    <a href="admin_trucks.php" style="margin:0 15px; padding:8px 12px; background:#FF9800; color:white; text-decoration:none; border-radius:4px;">🚚 Trucks</a>
    <a href="admin_users.php" style="margin:0 15px; padding:8px 12px; background:#9C27B0; color:white; text-decoration:none; border-radius:4px;">👥 Users</a>
    <a href="admin_reports.php" style="margin:0 15px; padding:8px 12px; background:#F44336; color:white; text-decoration:none; border-radius:4px;">📊 Reports</a>
  </div>

  <h3>Create New Request</h3>
  <form id="create-form">
    <input type="hidden" name="csrf_token" value="<?= htmlspecialchars($_SESSION['csrf_token']) ?>">
    <select id="area-select" name="area_id" required>
      <option value="">Loading areas...</option>
    </select>
    <button type="submit">Submit Request</button>
  </form>

  <button id="load-table" type="button" style="margin:10px 0;">Refresh Table</button>

  <h3><?= $isAdmin ? 'All Requests' : 'My Requests' ?></h3>
  <table id="requests-table" border="1" style="width:100%;">
    <thead>
      <tr>
        <th>ID</th>
        <th>Area</th>
        <th>Status</th>
        <th>Actions</th>
      </tr>
    </thead>
    <tbody>
      <tr>
        <td colspan="4">Loading...</td>
      </tr>
    </tbody>
  </table>

  <div id="edit-modal" style="display:none; position:fixed; top:20%; left:20%; background:white; border:2px solid #ccc; padding:20px; z-index:1000;">
    <h4>Edit Request</h4>
    <input type="hidden" id="edit-id">
    <label>Area: <select id="edit-area_id" required>
        <option value="">Select Area</option>
      </select></label><br>
    <label>Status:
      <select id="edit-status">
        <option value="pending">Pending</option>
        <option value="assigned">Assigned</option>
        <option value="completed">Completed</option>
        <option value="cancelled">Cancelled</option>
      </select>
    </label><br>
    <button type="button" onclick="saveEdit()">Save</button>
    <button type="button" onclick="closeModal()">Cancel</button>
  </div>
</div>

<script src="js/script.js"></script>
<?php include 'includes/footer.php'; ?>
