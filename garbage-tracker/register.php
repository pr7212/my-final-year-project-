<?php
session_start();
include '../config/db.php'; // connect to your database

if ($_SERVER['REQUEST_METHOD'] === 'POST') {

  // CSRF check
  if (!isset($_POST['csrf_token']) || $_POST['csrf_token'] !== $_SESSION['csrf_token']) {
    die('Invalid CSRF token');
  }

  $name = trim($_POST['name']);
  $email = trim($_POST['email']);
  $password = $_POST['password'];
  $confirm_password = $_POST['confirm_password'];

  // Password match check
  if ($password !== $confirm_password) {
    header("Location: ../register.php?error=mismatch");
    exit();
  }

  // Password strength check
  if (strlen($password) < 8) {
    header("Location: ../register.php?error=weak");
    exit();
  }

  // Check if email exists
  $stmt = $conn->prepare("SELECT id FROM users WHERE email=?");
  $stmt->bind_param("s", $email);
  $stmt->execute();
  $stmt->store_result();

  if ($stmt->num_rows > 0) {
    $stmt->close();
    header("Location: ../register.php?error=exists");
    exit();
  }
  $stmt->close();

  // Insert new user
  $hashed_password = password_hash($password, PASSWORD_DEFAULT);
  $stmt = $conn->prepare("INSERT INTO users (name, email, password) VALUES (?, ?, ?)");
  $stmt->bind_param("sss", $name, $email, $hashed_password);

  if ($stmt->execute()) {
    $stmt->close();
    $conn->close();
    header("Location: ../register.php?success=1");
    exit();
  } else {
    $stmt->close();
    $conn->close();
    header("Location: ../register.php?error=failed");
    exit();
  }
}
