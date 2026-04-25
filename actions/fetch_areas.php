<?php
session_start();
require '../config/db.php';

require_once '../includes/auth.php';

header('Content-Type: application/json');

function respond($success, $message, $data = [])
{
  http_response_code($success ? 200 : 400);
  echo json_encode([
    'success' => $success,
    'message' => $message,
    'data' => $data
  ]);
  exit();
}

requireRole(['admin', 'officer', 'resident', 'collector']);

$stmt = $conn->prepare('SELECT id, name FROM areas ORDER BY name');
if (!$stmt) {
  respond(false, 'Database error');
}

$stmt->execute();
$result = $stmt->get_result();

$data = [];
while ($row = $result->fetch_assoc()) {
  $data[] = [
    'id' => (int) $row['id'],
    'name' => htmlspecialchars($row['name'])
  ];
}

$stmt->close();
$conn->close();

respond(true, 'Areas fetched', $data);
