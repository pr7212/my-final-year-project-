<?php
include 'includes/auth.php';
requireRole('officer');

if (empty($_SESSION['csrf_token'])) {
  $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
}

$pageTitle = 'Officer Dashboard';
include 'includes/header.php';
?>
<div class="container">
  <div id="feedback" style="display:none; padding:10px; margin:10px 0;"></div>

  <h2>All Requests (Officer View)</h2>
  <p><em>Monitor all garbage collection requests across the system.</em></p>

  <button id="load-table" type="button" style="margin:10px 0;">Refresh All Requests</button>

  <h3>System Requests</h3>
  <table id="requests-table" border="1" style="width:100%;">
    <thead>
      <tr>
        <th>ID</th>
        <th>Area</th>
        <th>Status</th>
        <th>User ID</th>
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

<script>
  document.body.dataset.role = 'officer';
</script>
<script src="js/script.js"></script>
<?php include 'includes/footer.php'; ?>
