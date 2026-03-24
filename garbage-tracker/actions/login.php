<?php
// actions/login.php
session_start();
include '../config/db.php';

header('Content-Type: application/json');

// 1. Validate inputs exist and aren't empty
if (empty($_POST['email']) || empty($_POST['password'])) {
  http_response_code(400);
  echo json_encode(["status" => "error", "message" => "Email and password are required."]);
  exit;
}

$email    = trim($_POST['email']);
$password = $_POST['password'];

// 2. Validate email format
if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
  http_response_code(400);
  echo json_encode(["status" => "error", "message" => "Invalid email format."]);
  exit;
}

// 3. Use prepared statement — prevents SQL injection
$stmt = $conn->prepare("SELECT id, role, password FROM users WHERE email = ?");
$stmt->bind_param("s", $email);
$stmt->execute();
$result = $stmt->get_result();

if ($result->num_rows === 1) {
  $row = $result->fetch_assoc();

  if (password_verify($password, $row['password'])) {
    // 4. Regenerate session ID to prevent session fixation
    session_regenerate_id(true);

    $_SESSION['user_id'] = $row['id'];
    $_SESSION['role']    = $row['role'];

    echo json_encode(["status" => "success", "role" => $row['role']]);
  } else {
    http_response_code(401);
    echo json_encode(["status" => "error", "message" => "Invalid credentials."]);
  }
} else {
  // Same message for both "not found" & "wrong password" — avoids user enumeration
  http_response_code(401);
  echo json_encode(["status" => "error", "message" => "Invalid credentials."]);
}

$stmt->close();
$conn->close();  // close the DB connection
