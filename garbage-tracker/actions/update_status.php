<?php
session_start();
include '../config/db.php';

// Set JSON response header
header('Content-Type: application/json');

// ─── 1. AUTH CHECK ─────────────────────────────────────────────────────────────
// Bug fix: check isset() before accessing $_SESSION keys to avoid undefined index
if (!isset($_SESSION['role']) || !in_array($_SESSION['role'], ['admin', 'staff'])) {
  http_response_code(403);
  echo json_encode(['success' => false, 'message' => 'Access denied']);
  exit;
}

// ─── 2. INPUT VALIDATION ───────────────────────────────────────────────────────
// Bug fix: validate that required POST fields exist
if (!isset($_POST['request_id']) || !isset($_POST['status'])) {
  http_response_code(400);
  echo json_encode(['success' => false, 'message' => 'Missing required fields: request_id and status']);
  exit;
}

$request_id = $_POST['request_id'];
$status     = $_POST['status'];

// Bug fix: whitelist allowed status values — never trust raw user input
$allowed_statuses = ['pending', 'in_progress', 'completed', 'cancelled'];
if (!in_array($status, $allowed_statuses)) {
  http_response_code(400);
  echo json_encode([
    'success' => false,
    'message' => 'Invalid status value. Allowed: ' . implode(', ', $allowed_statuses)
  ]);
  exit;
}

// Bug fix: ensure request_id is a positive integer
$request_id = intval($request_id);
if ($request_id <= 0) {
  http_response_code(400);
  echo json_encode(['success' => false, 'message' => 'Invalid request_id']);
  exit;
}

// ─── 3. DB CONNECTION CHECK ────────────────────────────────────────────────────
// Bug fix: verify $conn exists before using it
if (!$conn) {
  http_response_code(500);
  echo json_encode(['success' => false, 'message' => 'Database connection failed']);
  exit;
}

// ─── 4. PREPARED STATEMENT (prevents SQL injection) ───────────────────────────
// Bug fix: original code used raw variables directly in SQL — highly vulnerable
$sql  = "UPDATE requests SET status = ? WHERE id = ?";
$stmt = $conn->prepare($sql);

if (!$stmt) {
  http_response_code(500);
  echo json_encode(['success' => false, 'message' => 'Query preparation failed: ' . $conn->error]);
  exit;
}

// 's' = string, 'i' = integer
$stmt->bind_param('si', $status, $request_id);

if ($stmt->execute()) {
  if ($stmt->affected_rows > 0) {
    echo json_encode(['success' => true, 'message' => 'Status updated successfully']);
  } else {
    // Query ran fine but no row matched that ID
    http_response_code(404);
    echo json_encode(['success' => false, 'message' => 'No request found with that ID']);
  }
} else {
  http_response_code(500);
  echo json_encode(['success' => false, 'message' => 'Update failed: ' . $stmt->error]);
}

$stmt->close();
$conn->close();
