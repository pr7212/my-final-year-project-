<?php
session_start();
require_once '../config/db.php';
require_once '../includes/auth.php';

header('Content-Type: application/json');

requireRole(['resident']);

// CSRF Check
if (
  empty($_SESSION['csrf_token']) ||
  empty($_POST['csrf_token']) ||
  !hash_equals($_SESSION['csrf_token'], $_POST['csrf_token'])
) {
  http_response_code(403);
  echo json_encode(['success' => false, 'message' => 'Invalid CSRF token']);
  exit();
}

$user_id = (int) $_SESSION['user_id'];
$location = trim($_POST['location'] ?? '');
$description = trim($_POST['description'] ?? '');

if (empty($location)) {
  echo json_encode(['success' => false, 'message' => 'Location required']);
  exit();
}

$stmt = $conn->prepare("INSERT INTO reports (user_id, location, description, status) VALUES (?, ?, ?, 'pending')");
$stmt->bind_param("iss", $user_id, $location, $description);

if ($stmt->execute()) {
  echo json_encode(['success' => true, 'message' => 'Report submitted']);
} else {
  echo json_encode(['success' => false, 'message' => 'Database error']);
}

$stmt->close();
$conn->close();
