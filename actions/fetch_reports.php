<?php
session_start();
require_once '../config/db.php';
require_once '../includes/auth.php';

header('Content-Type: application/json');

$user_id = (int) $_SESSION['user_id'];
$role = $_SESSION['role'] ?? 'resident';

function respond($success, $message, $data = [], $code = 200)
{
  http_response_code($code);
  echo json_encode([
    'success' => $success,
    'message' => $message,
    'data' => $data
  ]);
  exit();
}

if ($role === 'admin' || $role === 'officer') {
  // All reports
  $sql = "SELECT r.id, r.user_id, u.name as user_name, r.location, r.description, r.status, r.created_at
          FROM reports r
          JOIN users u ON r.user_id = u.id
          ORDER BY r.created_at DESC";
  $stmt = $conn->prepare($sql);
} elseif ($role === 'resident') {
  // Own reports
  $sql = "SELECT id, user_id, location, description, status, created_at FROM reports WHERE user_id = ? ORDER BY created_at DESC";
  $stmt = $conn->prepare($sql);
  $stmt->bind_param('i', $user_id);
} else {
  respond(false, 'Invalid role', [], 403);
}

if (!$stmt || !$stmt->execute()) {
  respond(false, 'Query failed', [], 500);
}

$result = $stmt->get_result();
$data = [];

while ($row = $result->fetch_assoc()) {
  $data[] = [
    'id' => (int)$row['id'],
    'user_name' => $row['user_name'] ?? 'Unknown',
    'user_id' => (int)$row['user_id'],
    'location' => htmlspecialchars($row['location']),
    'description' => htmlspecialchars($row['description']),
    'status' => $row['status'],
    'created_at' => $row['created_at']
  ];
}

$stmt->close();
$conn->close();

respond(true, 'Reports fetched', $data);
