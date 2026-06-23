<?php

/**
 * Login Processing Script
 *
 * Validates user credentials via POST, performs authentication,
 * sets session variables, rotates CSRF token, and redirects based on role.
 * Supports both standard form submissions and AJAX/JSON requests.
 */
session_start();
require_once __DIR__ . '/../config/db.php';

/**
 * Determine if the client expects a JSON response.
 *
 * Checks for XMLHttpRequest header or an Accept header containing application/json.
 *
 * @return bool True if the request prefers JSON, false otherwise.
 */
function wants_json_response()
{
  $requestedWith = $_SERVER['HTTP_X_REQUESTED_WITH'] ?? '';
  $accept = $_SERVER['HTTP_ACCEPT'] ?? '';

  return strtolower($requestedWith) === 'xmlhttprequest'
    || stripos($accept, 'application/json') !== false;
}

/**
 * Send a consistent response to the client.
 *
 * If the client expects JSON, output a JSON object and exit.
 * Otherwise, optionally redirect or output a plain text message.
 *
 * @param bool   $success  Indicates operation success.
 * @param string $message  Human-readable message.
 * @param mixed  $data     Optional data payload.
 * @param int    $code     HTTP status code.
 * @param string|null $redirect URL to redirect non-AJAX requests.
 */
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

  // Non-AJAX: redirect if URL provided
  if ($redirect !== null) {
    header('Location: ' . $redirect);
    exit();
  }

  // Fallback: set status code and output message
  http_response_code($code);
  exit($message);
}

// Only allow POST requests; reject others
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
  respond(false, 'Method not allowed', null, 405, '../index.php?error=method_not_allowed');
}

// Validate CSRF token presence and equality using timing-safe comparison
if (
  empty($_SESSION['csrf_token']) ||
  empty($_POST['csrf_token']) ||
  !hash_equals($_SESSION['csrf_token'], $_POST['csrf_token'])
) {
  respond(false, 'Invalid CSRF token', null, 403, '../index.php?error=invalid_csrf');
}

// Retrieve and sanitize input
$email = trim($_POST['email'] ?? '');
$password = $_POST['password'] ?? '';

// Basic required field check
if ($email === '' || $password === '') {
  respond(false, 'Email and password are required', null, 400, '../index.php?error=missing_fields');
}

// Validate email format
if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
  respond(false, 'Invalid email format', null, 400, '../index.php?error=invalid_email');
}

// Prepare a statement to fetch user by email
$stmt = $conn->prepare('SELECT id, name, role, password FROM users WHERE email = ? LIMIT 1');
if (!$stmt) {
  respond(false, 'Database error', null, 500, '../index.php?error=db_error');
}

$stmt->bind_param('s', $email);

// Execute query; handle execution failure
if (!$stmt->execute()) {
  $stmt->close();
  $conn->close();
  respond(false, 'Login failed', null, 500, '../index.php?error=db_error');
}

// Fetch the result row
$result = $stmt->get_result();
$user = $result ? $result->fetch_assoc() : null;

// Verify the password against the stored hash
if (!$user || !password_verify($password, $user['password'])) {
  $stmt->close();
  $conn->close();
  respond(false, 'Invalid credentials', null, 401, '../index.php?error=invalid_credentials');
}

// Regenerate session ID to prevent session fixation attacks
session_regenerate_id(true);

// Store user details in session
$_SESSION['user_id'] = (int) $user['id'];
$_SESSION['user_name'] = $user['name'];
$_SESSION['role'] = $user['role'];

// Rotate CSRF token after successful login to avoid token reuse
$_SESSION['csrf_token'] = bin2hex(random_bytes(32));

// Clean up database resources
$stmt->close();
$conn->close();

// Redirect user based on their role
switch ($user['role']) {

  case 'admin':
    header("Location: ../dashboard.php");
    break;

  case 'resident':
    header("Location: ../resident.php");
    break;

  case 'collector':
    header("Location: ../collector.php");
    break;

  case 'officer':
    header("Location: ../officer.php");
    break;

  default:
    header("Location: ../index.php");
}

exit();
