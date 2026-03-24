<?php
session_start();
include '../config/db.php';
header('Content-Type: application/json');

// 1. Auth check
if (!isset($_SESSION['user_id'])) {
  http_response_code(401);
  echo json_encode(['success' => false, 'message' => 'Unauthorized']);
  exit();
}

// 2. Validate
if (empty($_POST['request_id'])) {
  http_response_code(400);
  echo json_encode(['success' => false, 'message' => 'Missing request_id']);
  exit();
}

$request_id = intval($_POST['request_id']);
if ($request_id <= 0) {
  http_response_code(400);
  echo json_encode(['success' => false, 'message' => 'Invalid request_id']);
  exit();
}

// 3. Verify ownership
$user_id = intval($_SESSION['user_id']);
$stmt = $conn->prepare("DELETE FROM requests WHERE id = ? AND user_id = ?");
if (!$stmt) {
  http_response_code(500);
  echo json_encode(['success' => false, 'message' => 'DB error']);
  exit();
}

$stmt->bind_param('ii', $request_id, $user_id);
if ($stmt->execute() && $stmt->affected_rows > 0) {
  echo json_encode(['success' => true, 'message' => 'Request deleted']);
} else {
  http_response_code(404);
  echo json_encode(['success' => false, 'message' => 'Request not found or access denied']);
}

$stmt->close();
$conn->close();
