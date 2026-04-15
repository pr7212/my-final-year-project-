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
  respond(false, 'Method not allowed', null, 405, '../dashboard.php?error=method_not_allowed');
}

if (empty($_SESSION['user_id'])) {
  respond(false, 'Unauthorized', null, 401, '../index.php');
}

if (
  empty($_SESSION['csrf_token']) ||
  empty($_POST['csrf_token']) ||
  !hash_equals($_SESSION['csrf_token'], $_POST['csrf_token'])
) {
  respond(false, 'Invalid CSRF token', null, 403, '../dashboard.php?error=invalid_csrf');
}

$area = trim($_POST['area'] ?? '');

if ($area === '') {
  respond(false, 'Area is required', null, 400, '../dashboard.php?error=missing_area');
}

if (strlen($area) < 3) {
  respond(false, 'Area must be at least 3 characters long', null, 400, '../dashboard.php?error=area_too_short');
}

if (strlen($area) > 255) {
  respond(false, 'Area must be 255 characters or fewer', null, 400, '../dashboard.php?error=area_too_long');
}

$user_id = (int) $_SESSION['user_id'];
$status = 'pending';

$stmt = $conn->prepare('INSERT INTO requests (user_id, area, status) VALUES (?, ?, ?)');
if (!$stmt) {
  respond(false, 'Database error', null, 500, '../dashboard.php?error=db_error');
}

$stmt->bind_param('iss', $user_id, $area, $status);

if (!$stmt->execute()) {
  $stmt->close();
  $conn->close();
  respond(false, 'Insert failed', null, 500, '../dashboard.php?error=insert_failed');
}

$newId = $stmt->insert_id;

$stmt->close();
$conn->close();

respond(true, 'Request created successfully', [
  'id' => $newId,
  'area' => $area,
  'status' => $status
], 201, '../dashboard.php?success=request_submitted');
