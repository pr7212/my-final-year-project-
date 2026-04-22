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

  <h3 style="margin-top:40px;">🚚 Assign Trucks to Areas</h3>
  <form action="actions/assign_truck.php" method="POST" style="background:var(--surface); padding:20px; border-radius:var(--radius-md); border:1px solid var(--surface-border);">
    <input type="hidden" name="csrf_token" value="<?= htmlspecialchars($_SESSION['csrf_token']) ?>">
    <label>Area:
      <select id="area-select" name="area_id" required>
        <option value="">Loading areas...</option>
      </select>
    </label><br><br>
    <label>Truck:
      <select id="truck-select" name="truck_id" required>
        <option value="">Loading trucks...</option>
      </select>
    </label><br><br>
    <button type="submit" class="btn">Assign Truck</button>
  </form>

</div>

<script>
  document.body.dataset.role = 'officer';

  async function loadOptions() {
    try {
      const areaRes = await fetch('actions/fetch_areas.php');
      const areaData = await areaRes.json();
      if (areaData.success) {
        const areaSelect = document.getElementById('area-select');
        areaSelect.innerHTML = '<option value="">Select Area</option>';
        areaData.data.forEach(area => {
          areaSelect.innerHTML += `<option value="${area.id}">${area.name}</option>`;
        });
      }

      const truckRes = await fetch('actions/fetch_trucks.php');
      const truckData = await truckRes.json();
      if (truckData.success) {
        const truckSelect = document.getElementById('truck-select');
        truckSelect.innerHTML = '<option value="">Select Truck</option>';
        truckData.data.forEach(truck => {
          truckSelect.innerHTML += `<option value="${truck.id}">${truck.name} (${truck.status})</option>`;
        });
      }
    } catch (e) {
      console.error('Error loading options:', e);
    }
  }

  document.addEventListener('DOMContentLoaded', loadOptions);
</script>
<script src="js/script.js"></script>
<?php include 'includes/footer.php'; ?>
