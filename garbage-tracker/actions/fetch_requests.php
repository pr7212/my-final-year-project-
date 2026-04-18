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
    'count'   => count($data),
    'data'    => $data
  ]);

  exit();
}

/* ---------------------------
   AUTH CHECK
----------------------------*/
if (empty($_SESSION['user_id'])) {
  respond(false, 'Unauthorized access', [], 401);
}

$user_id = (int) $_SESSION['user_id'];
$role    = $_SESSION['role'] ?? 'resident';

/* ---------------------------
   PREPARE QUERY BY ROLE
----------------------------*/
if ($role === 'admin' || $role === 'officer') {

  // Admin + Officer can see all reports
  $sql = "
        SELECT id, user_id, area, status, timestamp
        FROM requests
        ORDER BY timestamp DESC
    ";

  $stmt = $conn->prepare($sql);
} elseif ($role === 'collector') {

  // Collectors only see assigned jobs
  $sql = "
        SELECT id, user_id, area, status, timestamp
        FROM requests
        WHERE status = 'assigned'
        ORDER BY timestamp DESC
    ";

  $stmt = $conn->prepare($sql);
} elseif ($role === 'resident') {

  // Residents only see their own requests
  $sql = "
        SELECT id, user_id, area, status, timestamp
        FROM requests
        WHERE user_id = ?
        ORDER BY timestamp DESC
    ";

  $stmt = $conn->prepare($sql);
} else {
  respond(false, 'Invalid user role', [], 403);
}

/* ---------------------------
   CHECK PREPARE
----------------------------*/
if (!$stmt) {
  respond(false, 'Database prepare failed', [], 500);
}

/* ---------------------------
   BIND PARAMS
----------------------------*/
if ($role === 'resident') {
  $stmt->bind_param('i', $user_id);
}

/* ---------------------------
   EXECUTE
----------------------------*/
if (!$stmt->execute()) {
  $stmt->close();
  $conn->close();
  respond(false, 'Query execution failed', [], 500);
}

/* ---------------------------
   FETCH RESULTS
----------------------------*/
$result = $stmt->get_result();

if (!$result) {
  $stmt->close();
  $conn->close();
  respond(false, 'Failed to fetch data', [], 500);
}

$data = [];

while ($row = $result->fetch_assoc()) {
  $data[] = [
    'id'        => (int) $row['id'],
    'user_id'   => (int) $row['user_id'],
    'area'      => htmlspecialchars($row['area']),
    'status'    => htmlspecialchars($row['status']),
    'timestamp' => $row['timestamp']
  ];
}

/* ---------------------------
   CLEANUP
----------------------------*/
$stmt->close();
$conn->close();

/* ---------------------------
   RESPONSE
----------------------------*/
respond(true, 'Requests fetched successfully', $data);
