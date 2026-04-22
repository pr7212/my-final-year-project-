<?php
session_start();
require '../config/db.php';

function wants_json_response()
{
  $requestedWith = $_SERVER['HTTP_X_REQUESTED_WITH'] ?? '';
  $accept = $_SERVER['HTTP_ACCEPT'] ?? '';

  return strtolower($requestedWith) === 'xmlhttprequest'
    || stripos($accept, 'application/json') !== false;
}

function respond($success, $message, $data = null, $code = 200, $redirect = null)
{
  if (wants_json_response()) {
    http_response_code($code);
    header('Content-Type: application/json');

    $response = [
      'success' => $success,
      'message' => $message
    ];

    if ($data !== null) {
      $response['data'] = $data;
    }

    echo json_encode($response);
    exit();
  }

  if ($redirect !== null) {
    header('Location: ' . $redirect);
    exit();
  }

  http_response_code($code);
  exit($message);
}

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
  respond(false, 'Method not allowed', null, 405, '../resident.php?error=method_not_allowed');
}

if (empty($_SESSION['user_id'])) {
  respond(false, 'Unauthorized', null, 401, '../index.php');
}

$role = $_SESSION['role'] ?? 'resident';

if (
  empty($_SESSION['csrf_token']) ||
  empty($_POST['csrf_token']) ||
  !hash_equals($_SESSION['csrf_token'], $_POST['csrf_token'])
) {
  respond(false, 'Invalid CSRF token', null, 403, '../resident.php?error=invalid_csrf');
}

$request_id = (int)($_POST['request_id'] ?? 0);
$area_id = (int)($_POST['area_id'] ?? 0);
$status = $_POST['status'] ?? '';

if ($request_id <= 0 || $area_id <= 0 || empty($status)) {
  respond(false, 'Invalid input', null, 400, '../resident.php?error=invalid_input');
}

$user_id = (int) $_SESSION['user_id'];

if ($role === 'admin' || $role === 'officer') {
  // Admins/officers can edit any request
  $sql = "
    UPDATE requests
    SET area_id = ?, status = ?, updated_at = CURRENT_TIMESTAMP
    WHERE id = ?
  ";
  $stmt = $conn->prepare($sql);
  if (!$stmt) {
    respond(false, 'Database error', null, 500, '../admin.php?error=db_error');
  }
  $stmt->bind_param('isi', $area_id, $status, $request_id);
} else {
  // Residents can only edit their own pending requests
  $resident_allowed_statuses = ['pending', 'cancelled'];
  if (!in_array($status, $resident_allowed_statuses, true)) {
    respond(false, 'Invalid status for resident', null, 403, '../resident.php?error=invalid_status');
  }

  $sql = "
    UPDATE requests
    SET area_id = ?, status = ?, updated_at = CURRENT_TIMESTAMP
    WHERE id = ? AND user_id = ?
  ";
  $stmt = $conn->prepare($sql);
  if (!$stmt) {
    respond(false, 'Database error', null, 500, '../resident.php?error=db_error');
  }
  $stmt->bind_param('isii', $area_id, $status, $request_id, $user_id);
}

if (!$stmt->execute()) {
  $stmt->close();
  $conn->close();
  respond(false, 'Update failed', null, 500, '../resident.php?error=update_failed');
}

$affected = $stmt->affected_rows;
$stmt->close();
$conn->close();

if ($affected === 0) {
  respond(false, 'Request not found or unauthorized', null, 403);
}

respond(true, 'Request updated successfully', ['affected_rows' => $affected]);
