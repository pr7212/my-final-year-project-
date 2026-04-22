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

function getDashboardUrl()
{
  $role = $_SESSION['role'] ?? 'guest';
  $maps = [
    'admin' => 'admin.php',
    'resident' => 'resident.php',
    'collector' => 'collector.php',
    'officer' => 'officer.php'
  ];
  return $maps[$role] ?? 'dashboard.php';
}
?>
<!DOCTYPE html>
<html lang="en">

<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title><?= $fullPageTitle ?></title>
  <link rel="preconnect" href="https://fonts.googleapis.com">
  <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
  <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700&family=Outfit:wght@400;600;800&display=swap" rel="stylesheet">
  <link rel="stylesheet" href="css/style.css">
</head>

<body>
  <nav>
    <span><strong>Garbage Tracker</strong></span>
    <span>
      <?php if (isset($_SESSION['user_id'])): ?>
        Welcome, <?= htmlspecialchars($_SESSION['user_name'] ?? 'User') ?>
        | <a href="<?= getDashboardUrl() ?>">Dashboard</a>
        |
        <form id="logout-form" action="actions/logout.php" method="POST" style="display:inline; margin:0; padding:0;">
          <input type="hidden" name="csrf_token" value="<?= htmlspecialchars($_SESSION['csrf_token'] ?? '') ?>">
          <button type="submit" style="background:none;border:none;color:blue;text-decoration:underline;cursor:pointer;padding:0;">Logout</button>
        </form>
      <?php else: ?>
        <a href="index.php">Login</a> |
        <a href="register.php">Register</a>
      <?php endif; ?>
    </span>
  </nav>
