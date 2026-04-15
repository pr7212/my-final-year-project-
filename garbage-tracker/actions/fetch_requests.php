<?php
session_start();
require '../config/db.php';

header('Content-Type: application/json');

function respond($success, $message, $data = [], $code = 200)
{
  http_response_code($code);
  echo json_encode([
    'success' => $success,
    'message' => $message,
    'count' => count($data),
    'data' => $data
  ]);
  exit();
}

if (empty($_SESSION['user_id'])) {
  respond(false, 'Unauthorized', [], 401);
}

$user_id = (int) $_SESSION['user_id'];
$isAdmin = ($_SESSION['role'] ?? 'user') === 'admin';

if ($isAdmin) {
  $stmt = $conn->prepare(
    'SELECT id, user_id, area, status, timestamp
     FROM requests
     ORDER BY timestamp DESC'
  );
} else {
  $stmt = $conn->prepare(
    'SELECT id, user_id, area, status, timestamp
     FROM requests
     WHERE user_id = ?
     ORDER BY timestamp DESC'
  );
}

if (!$stmt) {
  respond(false, 'Database error', [], 500);
}

if (!$isAdmin) {
  $stmt->bind_param('i', $user_id);
}

if (!$stmt->execute()) {
  $stmt->close();
  $conn->close();
  respond(false, 'Query execution failed', [], 500);
}

$result = $stmt->get_result();
if (!$result) {
  $stmt->close();
  $conn->close();
  respond(false, 'Failed to fetch data', [], 500);
}

$data = [];
while ($row = $result->fetch_assoc()) {
  $data[] = [
    'id' => (int) $row['id'],
    'user_id' => (int) $row['user_id'],
    'area' => $row['area'],
    'status' => $row['status'],
    'timestamp' => $row['timestamp']
  ];
}

$stmt->close();
$conn->close();

respond(true, 'Requests fetched successfully', $data);
