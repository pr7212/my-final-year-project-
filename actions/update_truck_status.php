<?php
session_start();
header('Content-Type: application/json');
include '../config/db.php';

if (!isset($_SESSION['user_id'])) {
  echo json_encode(['success' => false, 'message' => 'Unauthorized']);
  exit();
}

require_once '../includes/auth.php';
requireRole('admin');

$input = json_decode(file_get_contents('php://input'), true);

$csrf = $input['csrf_token'] ?? '';
if (empty($_SESSION['csrf_token']) || !hash_equals($_SESSION['csrf_token'], $csrf)) {
  echo json_encode(['success' => false, 'message' => 'Invalid CSRF token']);
  exit();
}

$id = (int)($input['id'] ?? 0);
$status = trim($input['status'] ?? '');

if ($id <= 0 || !in_array($status, ['available', 'busy', 'maintenance'])) {
  echo json_encode(['success' => false, 'message' => 'Invalid input']);
  exit();
}

$stmt = $conn->prepare("UPDATE trucks SET status = ? WHERE id = ?");
$stmt->bind_param("si", $status, $id);

if ($stmt->execute()) {
  if ($stmt->affected_rows > 0) {
    echo json_encode(['success' => true]);
  } else {
    echo json_encode(['success' => false, 'message' => 'Truck not found']);
  }
} else {
  echo json_encode(['success' => false, 'message' => 'Database error']);
}

$stmt->close();
$conn->close();
