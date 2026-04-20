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

$location = trim($_POST['location'] ?? '');
$collection_date = $_POST['collection_date'] ?? '';

if (empty($location) || empty($collection_date)) {
  echo json_encode(['success' => false, 'message' => 'Missing required fields']);
  exit();
}

$stmt = $conn->prepare("INSERT INTO schedules (location, collection_date, status) VALUES (?, ?, 'scheduled')");
$stmt->bind_param("ss", $location, $collection_date);

if ($stmt->execute()) {
  echo json_encode(['success' => true, 'message' => 'Schedule created']);
} else {
  echo json_encode(['success' => false, 'message' => 'Database error']);
}

$stmt->close();
$conn->close();
