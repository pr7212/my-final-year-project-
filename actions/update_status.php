<?php
session_start();
require '../config/db.php';

header('Content-Type: application/json');

function respond($success, $message, $code = 200)
{
  http_response_code($code);
  echo json_encode([
    'success' => $success,
    'message' => $message
  ]);
  exit();
}

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
  respond(false, 'Method not allowed', 405);
}

if (empty($_SESSION['role']) || !in_array($_SESSION['role'], ['admin', 'collector'], true)) {
  respond(false, 'Access denied', 403);
}

if (
  empty($_SESSION['csrf_token']) ||
  empty($_POST['csrf_token']) ||
  !hash_equals($_SESSION['csrf_token'], $_POST['csrf_token'])
) {
  respond(false, 'Invalid CSRF token', 403);
}

$request_id = filter_input(INPUT_POST, 'request_id', FILTER_VALIDATE_INT);
$status = trim($_POST['status'] ?? '');
$allowed_statuses = ['pending', 'assigned', 'completed', 'cancelled'];

if (!$request_id || $request_id <= 0) {
  respond(false, 'Invalid request_id', 400);
}

if (!in_array($status, $allowed_statuses, true)) {
  respond(false, 'Invalid status value', 400);
}

$stmt = $conn->prepare('UPDATE requests SET status = ? WHERE id = ?');
if (!$stmt) {
  respond(false, 'Query preparation failed', 500);
}

$stmt->bind_param('si', $status, $request_id);

if (!$stmt->execute()) {
  $stmt->close();
  $conn->close();
  respond(false, 'Update failed', 500);
}

$message = $stmt->affected_rows > 0
  ? 'Status updated successfully'
  : 'No changes made or request not found';

$stmt->close();
$conn->close();

respond(true, $message);
