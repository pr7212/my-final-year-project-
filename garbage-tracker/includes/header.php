<?php
if (session_status() === PHP_SESSION_NONE) {
  session_start();
}

$fullPageTitle = isset($pageTitle)
  ? htmlspecialchars($pageTitle) . ' | Garbage Tracker'
  : 'Garbage Tracker';

$logoutUrl = 'actions/logout.php';
if (!empty($_SESSION['csrf_token'])) {
  $logoutUrl .= '?csrf_token=' . urlencode($_SESSION['csrf_token']);
}
?>
<!DOCTYPE html>
<html lang="en">

<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title><?= $fullPageTitle ?></title>
  <link rel="stylesheet" href="css/style.css">
</head>

<body>
  <nav>
    <span><strong>Garbage Tracker</strong></span>
    <span>
      <?php if (isset($_SESSION['user_id'])): ?>
        Welcome, <?= htmlspecialchars($_SESSION['user_name'] ?? 'User') ?>
        | <a href="dashboard.php"><?= (isset($_SESSION['role']) && $_SESSION['role'] === 'admin') ? 'Manage Requests' : 'My Requests' ?></a>
        | <a href="<?= htmlspecialchars($logoutUrl) ?>">Logout</a>
      <?php else: ?>
        <a href="index.php">Login</a> |
        <a href="register.php">Register</a>
      <?php endif; ?>
    </span>
  </nav>
