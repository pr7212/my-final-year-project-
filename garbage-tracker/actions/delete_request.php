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
if (!$request_id || $request_id <= 0) {
  respond(false, 'Invalid request_id', 400);
}

$user_id = (int) $_SESSION['user_id'];
$isAdmin = ($_SESSION['role'] ?? 'user') === 'admin';

if ($isAdmin) {
  $stmt = $conn->prepare('DELETE FROM requests WHERE id = ?');
} else {
  $stmt = $conn->prepare('DELETE FROM requests WHERE id = ? AND user_id = ?');
}

if (!$stmt) {
  respond(false, 'Database error', 500);
}

if ($isAdmin) {
  $stmt->bind_param('i', $request_id);
} else {
  $stmt->bind_param('ii', $request_id, $user_id);
}

if (!$stmt->execute()) {
  $stmt->close();
  $conn->close();
  respond(false, 'Delete failed', 500);
}

if ($stmt->affected_rows === 0) {
  $stmt->close();
  $conn->close();
  respond(false, 'Request not found or access denied', 404);
}

$stmt->close();
$conn->close();

respond(true, 'Request deleted');
