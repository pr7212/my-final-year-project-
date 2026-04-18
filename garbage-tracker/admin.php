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

  <h3>Create New Request</h3>
  <form id="create-form">
    <input type="hidden" name="csrf_token" value="<?= htmlspecialchars($_SESSION['csrf_token']) ?>">
    <input type="text" id="area-input" name="area" placeholder="Enter area for garbage pickup (e.g., Sector 7)" maxlength="255" required>
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
    <label>Area: <input type="text" id="edit-area" maxlength="255"></label><br>
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
