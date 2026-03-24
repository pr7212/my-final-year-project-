<?php
$pageTitle = "Dashboard";
include 'includes/header.php';
include 'includes/auth.php';
include 'config/db.php';

// Generate CSRF token if not set
if (empty($_SESSION['csrf_token'])) {
  $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
}
?>
<div class="container">
  <div id="feedback" style="display:none; padding:10px; margin:10px 0;"></div>

  <h2>My Dashboard</h2>

  <h3>Create New Request</h3>
  <form id="create-form">
    <input type="hidden" name="csrf_token" value="<?= htmlspecialchars($_SESSION['csrf_token']) ?>">
    <input type="text" id="area-input" name="area" placeholder="Enter area for garbage pickup (e.g., Sector 7)" maxlength="255" required>
    <button type="submit">Submit Request</button>
  </form>

  <button id="load-table" style="margin:10px 0;">Refresh Table</button>

  <h3>My Requests</h3>
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

  <!-- Edit Modal -->
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
    <button onclick="saveEdit()">Save</button>
    <button onclick="closeModal()">Cancel</button>
  </div>
</div>

<script src="/js/script.js"></script>
<?php include 'includes/footer.php'; ?>
