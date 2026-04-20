<?php
session_start();
require '../config/db.php';

function redirect_with_status($query)
{
  header('Location: ../dashboard.php?' . $query);
  exit();
}

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
  http_response_code(405);
  exit('Method not allowed');
}

if (empty($_SESSION['user_id'])) {
  header('Location: ../index.php');
  exit();
}

if (($_SESSION['role'] ?? '') !== 'admin') {
  http_response_code(403);
  exit('Access denied.');
}

if (
  empty($_SESSION['csrf_token']) ||
  empty($_POST['csrf_token']) ||
  !hash_equals($_SESSION['csrf_token'], $_POST['csrf_token'])
) {
  http_response_code(403);
  exit('Invalid CSRF token.');
}

$area_id = filter_input(INPUT_POST, 'area_id', FILTER_VALIDATE_INT, [
  'options' => ['min_range' => 1]
]);

$truck_id = filter_input(INPUT_POST, 'truck_id', FILTER_VALIDATE_INT, [
  'options' => ['min_range' => 1]
]);

if (!$area_id || !$truck_id) {
  redirect_with_status('error=invalid_input');
}

$checkArea = $conn->prepare('SELECT id FROM areas WHERE id = ? LIMIT 1');
$checkTruck = $conn->prepare('SELECT id FROM trucks WHERE id = ? LIMIT 1');

if (!$checkArea || !$checkTruck) {
  redirect_with_status('error=db_error');
}

$checkArea->bind_param('i', $area_id);
$checkArea->execute();
$checkArea->store_result();

$checkTruck->bind_param('i', $truck_id);
$checkTruck->execute();
$checkTruck->store_result();

if ($checkArea->num_rows === 0 || $checkTruck->num_rows === 0) {
  $checkArea->close();
  $checkTruck->close();
  $conn->close();
  redirect_with_status('error=not_found');
}

$checkArea->close();
$checkTruck->close();

$stmt = $conn->prepare('UPDATE areas SET assigned_truck_id = ? WHERE id = ?');
if (!$stmt) {
  $conn->close();
  redirect_with_status('error=db_error');
}

$stmt->bind_param('ii', $truck_id, $area_id);

if (!$stmt->execute()) {
  $stmt->close();
  $conn->close();
  redirect_with_status('error=assign_failed');
}

$result = $stmt->affected_rows > 0
  ? 'success=truck_assigned'
  : 'info=no_change';

$stmt->close();
$conn->close();

redirect_with_status($result);
