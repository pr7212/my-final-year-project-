<?php
session_start();

// 1. Validate CSRF token to prevent logout CSRF attacks
if (
  empty($_GET['csrf_token']) ||
  !isset($_SESSION['csrf_token']) ||
  !hash_equals($_SESSION['csrf_token'], $_GET['csrf_token'])
) {
  http_response_code(403);
  die("Invalid CSRF token.");
}

// 2. Clear the $_SESSION superglobal in memory
$_SESSION = [];

// 3. Expire and delete the session cookie in the browser
if (ini_get("session.use_cookies")) {
  $params = session_get_cookie_params();
  setcookie(
    session_name(),
    '',
    time() - 42000,
    $params["path"],
    $params["domain"],
    $params["secure"],
    $params["httponly"]
  );
}

// 4. Destroy the server-side session data
session_destroy();

// 5. Redirect with exit() — never omit exit() after header()
header("Location: ../index.php");
exit();
