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

if (empty($_SESSION['user_id'])) {
  respond(false, 'Unauthorized', 401);
}

if (
  empty($_SESSION['csrf_token']) ||
  empty($_POST['csrf_token']) ||
  !hash_equals($_SESSION['csrf_token'], $_POST['csrf_token'])
) {
  respond(false, 'Invalid CSRF token', 403);
}

$request_id = filter_input(INPUT_POST, 'request_id', FILTER_VALIDATE_INT);
$area = trim($_POST['area'] ?? '');
$status = trim($_POST['status'] ?? '');

if (!$request_id || $request_id <= 0) {
  respond(false, 'Invalid request_id', 400);
}

if (strlen($area) < 3 || strlen($area) > 255) {
  respond(false, 'Area must be 3-255 characters', 400);
}

$allowed_statuses = ['pending', 'assigned', 'completed', 'cancelled'];
if (!in_array($status, $allowed_statuses, true)) {
  respond(false, 'Invalid status', 400);
}

$user_id = (int) $_SESSION['user_id'];
$isAdmin = ($_SESSION['role'] ?? 'user') === 'admin';

if ($isAdmin) {
  $check = $conn->prepare('SELECT 1 FROM requests WHERE id = ? LIMIT 1');
} else {
  $check = $conn->prepare('SELECT 1 FROM requests WHERE id = ? AND user_id = ? LIMIT 1');
}

if (!$check) {
  respond(false, 'Database error', 500);
}

if ($isAdmin) {
  $check->bind_param('i', $request_id);
} else {
  $check->bind_param('ii', $request_id, $user_id);
}

$check->execute();
$check->store_result();

if ($check->num_rows === 0) {
  $check->close();
  $conn->close();
  respond(false, 'Request not found or access denied', 403);
}

$check->close();

if ($isAdmin) {
  $stmt = $conn->prepare('UPDATE requests SET area = ?, status = ? WHERE id = ?');
} else {
  $stmt = $conn->prepare('UPDATE requests SET area = ?, status = ? WHERE id = ? AND user_id = ?');
}

if (!$stmt) {
  $conn->close();
  respond(false, 'Database error', 500);
}

if ($isAdmin) {
  $stmt->bind_param('ssi', $area, $status, $request_id);
} else {
  $stmt->bind_param('ssii', $area, $status, $request_id, $user_id);
}

if (!$stmt->execute()) {
  $stmt->close();
  $conn->close();
  respond(false, 'Update failed', 500);
}

$message = $stmt->affected_rows > 0
  ? 'Request updated successfully'
  : 'No changes made';

$stmt->close();
$conn->close();

respond(true, $message);
