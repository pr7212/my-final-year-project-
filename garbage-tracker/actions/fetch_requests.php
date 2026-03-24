<?php
session_start();
include '../config/db.php';

// Always set JSON content type first for API endpoints
header('Content-Type: application/json');

// 1. Verify the user is logged in
if (!isset($_SESSION['user_id'])) {
  http_response_code(401);
  echo json_encode(['success' => false, 'message' => 'Unauthorised.']);
  exit();
}

$user_id = (int) $_SESSION['user_id'];
$role    = $_SESSION['role'] ?? 'citizen';

// 2. Admins see all requests; citizens see only their own
if ($role === 'admin') {
  $stmt = $conn->prepare(
    "SELECT id, user_id, area, status, timestamp
         FROM requests
         ORDER BY timestamp DESC"
  );
} else {
  $stmt = $conn->prepare(
    "SELECT id, area, status, timestamp
         FROM requests
         WHERE user_id = ?
         ORDER BY timestamp DESC"
  );
}

// 3. Check prepared statement succeeded
if (!$stmt) {
  http_response_code(500);
  echo json_encode(['success' => false, 'message' => 'Query preparation failed.']);
  exit();
}

// 4. Bind parameter only for non-admin queries
if ($role !== 'admin') {
  $stmt->bind_param("i", $user_id);
}

$stmt->execute();
$result = $stmt->get_result();

// 5. Check result is valid before looping
if (!$result) {
  http_response_code(500);
  echo json_encode(['success' => false, 'message' => 'Query failed.']);
  exit();
}

// 6. Build response with only the columns the frontend needs
$data = [];
while ($row = $result->fetch_assoc()) {
  $data[] = [
    'id'        => (int) $row['id'],
    'area'      => $row['area'],
    'status'    => $row['status'],
    'timestamp' => $row['timestamp'],
  ];
}

$stmt->close();

// 7. Always return a consistent response structure
echo json_encode([
  'success' => true,
  'count'   => count($data),
  'data'    => $data
]);
exit();
