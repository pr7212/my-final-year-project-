<?php
session_start();
include '../config/db.php';

// 1. Redirect if already logged in
if (isset($_SESSION['user_id'])) {
  header("Location: ../dashboard.php");
  exit();
}

// 2. Validate CSRF token
if (
  empty($_POST['csrf_token']) ||
  !isset($_SESSION['csrf_token']) ||
  !hash_equals($_SESSION['csrf_token'], $_POST['csrf_token'])
) {
  http_response_code(403);
  die("Invalid CSRF token.");
}

// 3. Check all required fields are present
if (
  empty($_POST['name']) ||
  empty($_POST['email']) ||
  empty($_POST['password']) ||
  empty($_POST['confirm_password'])
) {
  header("Location: ../register.php?error=missing_fields");
  exit();
}

// 4. Sanitize and validate inputs
$name  = trim($_POST['name']);
$email = trim($_POST['email']);
$password         = $_POST['password'];
$confirm_password = $_POST['confirm_password'];

if (strlen($name) < 2 || strlen($name) > 100) {
  header("Location: ../register.php?error=invalid_name");
  exit();
}

// 5. Validate email format
if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
  header("Location: ../register.php?error=invalid_email");
  exit();
}

// 6. Validate password length before hashing
if (strlen($password) < 8) {
  header("Location: ../register.php?error=weak");
  exit();
}

// 7. Validate password confirmation matches
if (!hash_equals($password, $confirm_password)) {
  header("Location: ../register.php?error=mismatch");
  exit();
}

// 8. Check for duplicate email using a prepared statement
$stmt = $conn->prepare("SELECT id FROM users WHERE email = ?");

if (!$stmt) {
  header("Location: ../register.php?error=db_error");
  exit();
}

$stmt->bind_param("s", $email);
$stmt->execute();
$stmt->store_result();

if ($stmt->num_rows > 0) {
  header("Location: ../register.php?error=exists");
  exit();
}
$stmt->close();

// 9. Hash password only after all validation passes
$hashed_password = password_hash($password, PASSWORD_DEFAULT);
$role = "user";

// 10. Insert using a prepared statement — prevents SQL injection
$stmt = $conn->prepare(
  "INSERT INTO users (name, email, password, role) VALUES (?, ?, ?, ?)"
);

if (!$stmt) {
  header("Location: ../register.php?error=db_error");
  exit();
}

$stmt->bind_param("ssss", $name, $email, $hashed_password, $role);

if ($stmt->execute()) {
  header("Location: ../index.php?success=registered");
} else {
  header("Location: ../register.php?error=failed");
}

$stmt->close();
exit(); // 11. Always exit after every header() redirect
