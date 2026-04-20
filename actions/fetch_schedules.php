<?php
session_start();
require_once '../config/db.php';
require_once '../includes/auth.php';

header('Content-Type: application/json');

requireRole(['admin']);

$user_id = (int) $_SESSION['user_id'];

// Admin can see all schedules
$sql = "SELECT id, location, collection_date, status, created_at FROM schedules ORDER BY collection_date ASC";
$stmt = $conn->prepare($sql);

if (!$stmt) {
  echo json_encode(['success' => false, 'message' => 'Prepare failed']);
  exit();
}

if (!$stmt->execute()) {
  echo json_encode(['success' => false, 'message' => 'Execute failed']);
  exit();
}

$result = $stmt->get_result();
$data = [];

while ($row = $result->fetch_assoc()) {
  $data[] = [
    'id' => (int) $row['id'],
    'location' => htmlspecialchars($row['location']),
    'collection_date' => $row['collection_date'],
    'status' => $row['status'],
    'created_at' => $row['created_at']
  ];
}

echo json_encode([
  'success' => true,
  'message' => 'Schedules fetched',
  'data' => $data,
  'count' => count($data)
]);

$stmt->close();
$conn->close();
