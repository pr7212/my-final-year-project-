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

// 2. Validate inputs
if (empty($_POST['request_id']) || empty($_POST['area']) || !isset($_POST['status'])) {
  http_response_code(400);
  echo json_encode(['success' => false, 'message' => 'Missing fields: request_id, area, status']);
  exit();
}

$request_id = intval($_POST['request_id']);
$area = trim($_POST['area']);
$status = $_POST['status'];

if ($request_id <= 0) {
  http_response_code(400);
  echo json_encode(['success' => false, 'message' => 'Invalid request_id']);
  exit();
}

if (strlen($area) < 3 || strlen($area) > 255) {
  http_response_code(400);
  echo json_encode(['success' => false, 'message' => 'Area must be 3-255 chars']);
  exit();
}

if (!in_array($status, ['pending', 'assigned', 'completed', 'cancelled'])) {
  http_response_code(400);
  echo json_encode(['success' => false, 'message' => 'Invalid status']);
  exit();
}

// 3. Verify ownership
$user_id = intval($_SESSION['user_id']);
$check_stmt = $conn->prepare("SELECT id FROM requests WHERE id = ? AND user_id = ?");
$check_stmt->bind_param('ii', $request_id, $user_id);
$check_stmt->execute();
if ($check_stmt->get_result()->num_rows === 0) {
  http_response_code(403);
  echo json_encode(['success' => false, 'message' => 'Request not found or access denied']);
  $check_stmt->close();
  exit();
}
$check_stmt->close();

// 4. Update
$stmt = $conn->prepare("UPDATE requests SET area = ?, status = ? WHERE id = ? AND user_id = ?");
if (!$stmt) {
  http_response_code(500);
  echo json_encode(['success' => false, 'message' => 'DB error: ' . $conn->error]);
  exit();
}

$stmt->bind_param('ssii', $area, $status, $request_id, $user_id);
if ($stmt->execute() && $stmt->affected_rows > 0) {
  echo json_encode(['success' => true, 'message' => 'Request updated']);
} else {
  echo json_encode(['success' => false, 'message' => 'Update failed']);
}

$stmt->close();
$conn->close();
