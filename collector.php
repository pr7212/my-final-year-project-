<?php
include 'includes/auth.php';
requireRole('collector');

$pageTitle = 'Collector Dashboard';
include 'includes/header.php';
?>
<div class="container">
  <div id="feedback" style="display:none; padding:10px; margin:10px 0;"></div>

  <h2>Assigned Collections (Collector)</h2>
  <p><em>View and complete your assigned garbage collection jobs.</em></p>

  <button id="load-table" type="button" style="margin:10px 0;">Refresh Assigned Jobs</button>

  <h3>Assigned Jobs</h3>
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
</div>

<script>
  document.body.dataset.role = 'collector';
</script>
<script src="js/script.js"></script>
<?php include 'includes/footer.php'; ?>
