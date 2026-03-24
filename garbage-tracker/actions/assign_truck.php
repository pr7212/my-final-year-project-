<?php
session_start();
include '../config/db.php';

// 1. Verify user is logged in first
if (!isset($_SESSION['user_id'])) {
  header("Location: ../index.php");
  exit();
}

// 2. Verify user is an admin
if (!isset($_SESSION['role']) || $_SESSION['role'] !== 'admin') {
  http_response_code(403);
  die("Access denied.");
}

// 3. Validate CSRF token
if (
  empty($_POST['csrf_token']) ||
  !hash_equals($_SESSION['csrf_token'], $_POST['csrf_token'])
) {
  http_response_code(403);
  die("Invalid CSRF token.");
}

// 4. Check that required POST fields are present
if (empty($_POST['area_id']) || empty($_POST['truck_id'])) {
  header("Location: ../admin/dashboard.php?error=missing_fields");
  exit();
}

// 5. Validate inputs are positive integers
$area_id  = filter_var($_POST['area_id'],  FILTER_VALIDATE_INT, ['options' => ['min_range' => 1]]);
$truck_id = filter_var($_POST['truck_id'], FILTER_VALIDATE_INT, ['options' => ['min_range' => 1]]);

if (!$area_id || !$truck_id) {
  header("Location: ../admin/dashboard.php?error=invalid_input");
  exit();
}

// 6. Use a prepared statement — prevents SQL injection
$stmt = $conn->prepare("UPDATE areas SET assigned_truck = ? WHERE id = ?");

if (!$stmt) {
  header("Location: ../admin/dashboard.php?error=db_error");
  exit();
}

$stmt->bind_param("ii", $truck_id, $area_id);

if ($stmt->execute() && $stmt->affected_rows > 0) {
  header("Location: ../admin/dashboard.php?success=truck_assigned");
} else {
  header("Location: ../admin/dashboard.php?error=assign_failed");
}

$stmt->close();
exit();
