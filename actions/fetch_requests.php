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
        SELECT r.id, r.user_id, r.status, r.created_at, a.name AS area_name, t.name AS truck_name
        FROM requests r
        LEFT JOIN areas a ON r.area_id = a.id
        LEFT JOIN trucks t ON a.assigned_truck_id = t.id
        ORDER BY r.created_at DESC
    ";

  $stmt = $conn->prepare($sql);
} elseif ($role === 'collector') {

  // Collectors only see assigned jobs
  $sql = "
        SELECT r.id, r.user_id, r.status, r.created_at, a.name AS area_name, t.name AS truck_name
        FROM requests r
        LEFT JOIN areas a ON r.area_id = a.id
        LEFT JOIN trucks t ON a.assigned_truck_id = t.id
        WHERE r.status = 'assigned'
        ORDER BY r.created_at DESC
    ";

  $stmt = $conn->prepare($sql);
} elseif ($role === 'resident') {

  // Residents only see their own requests
  $sql = "
        SELECT r.id, r.user_id, r.status, r.created_at, a.name AS area_name, t.name AS truck_name
        FROM requests r
        LEFT JOIN areas a ON r.area_id = a.id
        LEFT JOIN trucks t ON a.assigned_truck_id = t.id
        WHERE r.user_id = ?
        ORDER BY r.created_at DESC
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
    'area_name' => htmlspecialchars($row['area_name'] ?? 'Unknown'),
    'truck_name' => htmlspecialchars($row['truck_name'] ?? 'No truck'),
    'status'    => htmlspecialchars($row['status']),
    'created_at' => $row['created_at']
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
