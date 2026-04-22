<?php
session_start();
require_once '../config/db.php';
require_once '../includes/auth.php';

header('Content-Type: application/json');

requireRole(['admin', 'officer']);

$input = json_decode(file_get_contents('php://input'), true);

$csrf = $input['csrf_token'] ?? '';
if (empty($_SESSION['csrf_token']) || !hash_equals($_SESSION['csrf_token'], $csrf)) {
  echo json_encode(['success' => false, 'message' => 'Invalid CSRF token']);
  exit();
}

$id = (int)($input['id'] ?? 0);
$status = trim($input['status'] ?? '');

if ($id <= 0 || !in_array($status, ['pending', 'resolved'])) {
  echo json_encode(['success' => false, 'message' => 'Invalid input']);
  exit();
}

$stmt = $conn->prepare("UPDATE reports SET status = ? WHERE id = ?");
if (!$stmt) {
  echo json_encode(['success' => false, 'message' => 'Database error']);
  exit();
}

$stmt->bind_param("si", $status, $id);

if ($stmt->execute()) {
  echo json_encode(['success' => true, 'message' => 'Report status updated']);
} else {
  echo json_encode(['success' => false, 'message' => 'Failed to update']);
}

$stmt->close();
$conn->close();
