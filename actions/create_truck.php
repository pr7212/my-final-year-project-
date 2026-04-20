<?php
session_start();
require_once '../config/db.php';
require_once '../includes/auth.php';

header('Content-Type: application/json');

requireRole(['admin']);

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

$name = trim($_POST['name'] ?? '');

if (empty($name)) {
  echo json_encode(['success' => false, 'message' => 'Truck name required']);
  exit();
}

$stmt = $conn->prepare("INSERT INTO trucks (name, status) VALUES (?, 'available')");
$stmt->bind_param("s", $name);

if ($stmt->execute()) {
  echo json_encode(['success' => true, 'message' => 'Truck added', 'id' => $conn->insert_id]);
} else {
  echo json_encode(['success' => false, 'message' => 'Database error: ' . $conn->error]);
}

$stmt->close();
$conn->close();
