<?php
session_start();
include '../config/db.php';

// 1. Verify the user is logged in
if (!isset($_SESSION['user_id'])) {
  header("Location: ../index.php");
  exit();
}

// 2. Validate CSRF token
if (
  empty($_POST['csrf_token']) ||
  !hash_equals($_SESSION['csrf_token'], $_POST['csrf_token'])
) {
  http_response_code(403);
  die("Invalid CSRF token.");
}

// 3. Check required POST field exists
if (empty($_POST['area'])) {
  header("Location: ../dashboard.php?error=missing_area");
  exit();
}

// 4. Validate and sanitize input
$area = trim($_POST['area']);

if (strlen($area) < 3) {
  header("Location: ../dashboard.php?error=area_too_short");
  exit();
}

if (strlen($area) > 255) {
  header("Location: ../dashboard.php?error=area_too_long");
  exit();
}

// 5. Use session user_id — never trust POST for ownership
$user_id = (int) $_SESSION['user_id'];
$status  = "pending";

// 6. Use a prepared statement — prevents SQL injection
$stmt = $conn->prepare(
  "INSERT INTO requests (user_id, area, status) VALUES (?, ?, ?)"
);

if (!$stmt) {
  header("Location: ../dashboard.php?error=db_error");
  exit();
}

$stmt->bind_param("iss", $user_id, $area, $status);

if ($stmt->execute()) {
  header("Location: ../dashboard.php?success=1");
} else {
  header("Location: ../dashboard.php?error=insert_failed");
}

$stmt->close();
$conn->close(); // close DB connection
exit(); // 7. Always exit after every header() redirect
