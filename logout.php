<?php
session_start();

// 0. Validate CSRF token (logout protection)
if (
  empty($_GET['csrf_token']) ||
  empty($_SESSION['csrf_token']) ||
  !hash_equals($_SESSION['csrf_token'], $_GET['csrf_token'])
) {
  http_response_code(403);
  exit("Invalid CSRF token.");
}

// 1. Clear all session data
$_SESSION = [];

// 2. Remove session cookie (secure deletion)
if (ini_get("session.use_cookies")) {
  $params = session_get_cookie_params();

  setcookie(
    session_name(),
    '',
    time() - 3600,
    $params["path"],
    $params["domain"],
    $params["secure"],
    $params["httponly"]
  );
}

// 3. Destroy session on server
session_destroy();

// 4. Redirect user safely
header("Location: ../index.php?success=logged_out");
exit();
