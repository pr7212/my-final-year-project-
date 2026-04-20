<?php
session_start();
require_once '../config/db.php';
require_once '../includes/auth.php';

header('Content-Type: application/json');

requireRole(['admin']);

// CSRF Check
if (
  empty($_SESSION['csrf_token']) ||
  empty($_POST['csrf_token']) ||
  !hash_equals($_SESSION['csrf_token'], $_POST['csrf_token'])
) {
  http_response_code(403);
  echo json_encode(['success' => false, 'message' => 'Invalid CSRF token']);
  exit();
}

$name = trim($_POST['name'] ?? '');
$email = trim($_POST['email'] ?? '');
$password = $_POST['password'] ?? '';
$role = $_POST['role'] ?? '';

$valid_roles = ['resident', 'collector', 'officer', 'admin'];

if (empty($name) || empty($email) || empty($password) || empty($role) || !in_array($role, $valid_roles)) {
  echo json_encode(['success' => false, 'message' => 'All fields required, valid role']);
  exit();
}

if (strlen($password) < 8) {
  echo json_encode(['success' => false, 'message' => 'Password min 8 chars']);
  exit();
}

if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
  echo json_encode(['success' => false, 'message' => 'Invalid email']);
  exit();
}

// Check duplicate email
$check = $conn->prepare("SELECT id FROM users WHERE email = ?");
$check->bind_param("s", $email);
$check->execute();
if ($check->get_result()->num_rows > 0) {
  echo json_encode(['success' => false, 'message' => 'Email exists']);
  $check->close();
  exit();
}
$check->close();

$hashed_password = password_hash($password, PASSWORD_DEFAULT);

$stmt = $conn->prepare("INSERT INTO users (name, email, password, role) VALUES (?, ?, ?, ?)");
$stmt->bind_param("ssss", $name, $email, $hashed_password, $role);

if ($stmt->execute()) {
  echo json_encode(['success' => true, 'message' => 'User created', 'id' => $conn->insert_id]);
} else {
  echo json_encode(['success' => false, 'message' => 'Database error']);
}

$stmt->close();
$conn->close();
