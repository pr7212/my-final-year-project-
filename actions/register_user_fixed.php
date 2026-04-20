<?php
session_start();
require '../config/db.php';

header('Content-Type: application/json');

// 0. Redirect if already logged in
if (!empty($_SESSION['user_id'])) {
  header("Location: ../index.php");
  exit();
}

// 1. Ensure POST request
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
  http_response_code(405);
  exit("Method not allowed");
}

// 2. CSRF validation (hardened)
if (
  empty($_SESSION['csrf_token']) ||
  empty($_POST['csrf_token']) ||
  !hash_equals($_SESSION['csrf_token'], $_POST['csrf_token'])
) {
  http_response_code(403);
  exit("Invalid CSRF token.");
}

// 3. Collect + sanitize inputs
$name     = trim($_POST['name'] ?? '');
$email    = trim($_POST['email'] ?? '');
$password = $_POST['password'] ?? '';
$confirm  = $_POST['confirm_password'] ?? '';

// 4. Validate required fields
if ($name === '' || $email === '' || $password === '' || $confirm === '') {
  header("Location: ../register.php?error=missing_fields");
  exit();
}

// 5. Validate name
if (strlen($name) < 2 || strlen($name) > 100) {
  header("Location: ../register.php?error=invalid_name");
  exit();
}

// 6. Validate email
if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
  header("Location: ../register.php?error=invalid_email");
  exit();
}

// 7. Validate password strength
if (strlen($password) < 8) {
  header("Location: ../register.php?error=weak_password");
  exit();
}

// 8. Secure password match check
if (!hash_equals($password, $confirm)) {
  header("Location: ../register.php?error=password_mismatch");
  exit();
}

// 9. Check duplicate email
$stmt = $conn->prepare("SELECT id FROM users WHERE email = ? LIMIT 1");

if (!$stmt) {
  echo json_encode(["error" => $conn->error]);
  $conn->close();
  exit();
}

$stmt->bind_param("s", $email);

if (!$stmt->execute()) {
  echo json_encode(["error" => $stmt->error]);
  $stmt->close();
  $conn->close();
  exit();
}

$stmt->store_result();

if ($stmt->num_rows > 0) {
  header("Location: ../register.php?error=email_exists");
  exit();
}

$stmt->close();

// 10. Hash password (ONLY after validation)
$hashed_password = password_hash($password, PASSWORD_DEFAULT);
$role = "resident";
// 11. Insert user securely
$stmt = $conn->prepare(
  "INSERT INTO users (name, email, password, role) VALUES (?, ?, ?, ?)"
);

if (!$stmt) {
  echo json_encode(["error" => $conn->error]);
  $conn->close();
  exit();
}

$stmt->bind_param("ssss", $name, $email, $hashed_password, $role);

// 12. Execute
if ($stmt->execute()) {
  $stmt->close();
  $conn->close();
  header("Location: ../index.php?success=registered");
  exit();
} else {
  echo json_encode(["error" => $stmt->error]);
  $stmt->close();
  $conn->close();
  exit();
}
