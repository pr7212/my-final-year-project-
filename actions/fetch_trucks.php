<?php
session_start();
require_once '../config/db.php';
require_once '../includes/auth.php';

header('Content-Type: application/json');

requireRole(['admin', 'officer']);

$sql = "SELECT id, name, status, created_at FROM trucks ORDER BY name ASC";
$stmt = $conn->prepare($sql);

if (!$stmt || !$stmt->execute()) {
  echo json_encode(['success' => false, 'message' => 'Query failed']);
  exit();
}

$result = $stmt->get_result();
$data = [];

while ($row = $result->fetch_assoc()) {
  $data[] = [
    'id' => (int)$row['id'],
    'name' => htmlspecialchars($row['name']),
    'status' => $row['status'],
    'created_at' => $row['created_at']
  ];
}

echo json_encode([
  'success' => true,
  'message' => 'Trucks fetched',
  'data' => $data,
  'count' => count($data)
]);

$stmt->close();
$conn->close();
