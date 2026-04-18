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
  respond(false, 'Method not allowed', null, 405, '../index.php?error=method_not_allowed');
}

if (
  empty($_SESSION['csrf_token']) ||
  empty($_POST['csrf_token']) ||
  !hash_equals($_SESSION['csrf_token'], $_POST['csrf_token'])
) {
  respond(false, 'Invalid CSRF token', null, 403, '../index.php?error=invalid_csrf');
}

$email = trim($_POST['email'] ?? '');
$password = $_POST['password'] ?? '';

if ($email === '' || $password === '') {
  respond(false, 'Email and password are required', null, 400, '../index.php?error=missing_fields');
}

if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
  respond(false, 'Invalid email format', null, 400, '../index.php?error=invalid_email');
}

$stmt = $conn->prepare('SELECT id, name, role, password FROM users WHERE email = ? LIMIT 1');
if (!$stmt) {
  respond(false, 'Database error', null, 500, '../index.php?error=db_error');
}

$stmt->bind_param('s', $email);

if (!$stmt->execute()) {
  $stmt->close();
  $conn->close();
  respond(false, 'Login failed', null, 500, '../index.php?error=db_error');
}

$result = $stmt->get_result();
$user = $result ? $result->fetch_assoc() : null;

if (!$user || !password_verify($password, $user['password'])) {
  $stmt->close();
  $conn->close();
  respond(false, 'Invalid credentials', null, 401, '../index.php?error=invalid_credentials');
}

session_regenerate_id(true);

$_SESSION['user_id'] = (int) $user['id'];
$_SESSION['user_name'] = $user['name'];
$_SESSION['role'] = $user['role'];

$stmt->close();
$conn->close();

$redirect = '../dashboard.php';

switch ($user['role']) {
  case 'admin':
    $redirect = '../admin.php';
    break;

  case 'resident':
    $redirect = '../resident.php';
    break;

  case 'collector':
    $redirect = '../collector.php';
    break;

  case 'officer':
    $redirect = '../officer.php';
    break;

  default:
    $redirect = '../dashboard.php';
    break;
}

respond(true, 'Login successful', [
  'role' => $user['role'],
  'name' => $user['name']
], 200, $redirect);
