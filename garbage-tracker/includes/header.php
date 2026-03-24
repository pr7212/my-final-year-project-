<?php
// Ensure session is started before this header is included
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// Allow individual pages to set their own title
// Usage: <?php $pageTitle = "Dashboard"; include 'includes/header.php';
?>
$pageTitle = isset($pageTitle) ? htmlspecialchars($pageTitle) . ' | Garbage Tracker' : 'Garbage Tracker';
?>
<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= $pageTitle ?></title>

    <!-- Favicon -->
    <link rel="icon" type="image/x-icon" href="/favicon.ico">

    <!-- Absolute path from web root — works at any nesting level -->
    <link rel="stylesheet" href="/css/style.css">
</head>

<body>

    <!-- Navigation Bar -->
    <nav>
        <span><strong>🗑️ Garbage Tracker</strong></span>
        <span>
            <?php if (isset($_SESSION['user_id'])): ?>
                <!-- Show logged-in user info -->
                Welcome, <?= htmlspecialchars($_SESSION['user_name'] ?? 'User') ?>
                <?php if (isset($_SESSION['role']) && $_SESSION['role'] === 'admin'): ?>
                    | <a href="/admin/dashboard.php">Admin Panel</a>
                <?php else: ?>
                    | <a href="/dashboard.php">My Requests</a>
                <?php endif; ?>
                | <a href="/actions/logout.php">Logout</a>

            <?php else: ?>
                <!-- Show guest links -->
                <a href="/index.php">Login</a> |
                <a href="/register.php">Register</a>
            <?php endif; ?>
        </span>
    </nav>

    <!-- NOTE: This file opens <body>. Always include 'includes/footer.php'
     at the bottom of every page to close </body> and </html> properly. -->
