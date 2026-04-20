<?php
include 'includes/auth.php';
requireRole('admin');
// Admin-only dashboard

if (empty($_SESSION['csrf_token'])) {
  $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
}

$pageTitle = 'Dashboard';
$success = $_GET['success'] ?? '';
$error = $_GET['error'] ?? '';

$role = $_SESSION['role'] ?? 'resident';
$userName = $_SESSION['user_name'] ?? 'User';

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

  <h2>Welcome, <?= htmlspecialchars($userName) ?> (<?= ucfirst($role) ?>)</h2>

  <?php if ($role === 'admin'): ?>
    <div style="background:#e3f2fd; padding:15px; margin:10px 0; border-radius:5px;">
      👨‍💼 <strong>Admin Panel:</strong> <a href="admin.php">Manage All Requests & Trucks</a>
    </div>
  <?php elseif ($role === 'officer'): ?>
    <div style="background:#f3e5f5; padding:15px; margin:10px 0; border-radius:5px;">
      👮‍♂️ <strong>Officer View:</strong> <a href="officer.php">View All System Requests</a>
    </div>
  <?php elseif ($role === 'collector'): ?>
    <div style="background:#e8f5e8; padding:15px; margin:10px 0; border-radius:5px;">
      🚛 <strong>Collector Jobs:</strong> <a href="collector.php">My Assigned Collections</a>
    </div>
  <?php else: // resident
  ?>
    <div style="background:#fff3e0; padding:15px; margin:10px 0; border-radius:5px;">
      🏠 <strong>Resident Portal:</strong> <a href="resident.php">Submit & Track My Requests</a>
    </div>
  <?php endif; ?>

  <h3>Quick Request Summary</h3>
  <button id="load-table" type="button">Load My Recent Requests</button>

  <table id="requests-table" border="1" style="width:100%; margin-top:10px;">
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
        <td colspan="4">Click refresh to load your recent requests...</td>
      </tr>
    </tbody>
  </table>
</div>

<script>
  document.body.dataset.role = '<?= htmlspecialchars($role) ?>';
</script>
<script src="js/script.js"></script>
<?php include 'includes/footer.php'; ?>
