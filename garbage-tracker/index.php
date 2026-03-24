<?php
session_start(); // needed here ONLY to check login state + generate CSRF token

// If already logged in, skip the login page
if (isset($_SESSION['user_id'])) {
  header("Location: dashboard.php");
  exit();
}

// Generate CSRF token if not set
if (empty($_SESSION['csrf_token'])) {
  $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
}
?>
<!DOCTYPE html>
<html lang="en">

<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Login</title>
</head>

<body>
  <h2>Login</h2>

  <!-- Show error message if login failed -->
  <?php if (isset($_GET['error']) && $_GET['error'] === '1'): ?>
    <p style="color:red;">Invalid email or password. Please try again.</p>
  <?php endif; ?>

  <form action="actions/login.php" method="POST">

    <!-- CSRF protection -->
    <input type="hidden" name="csrf_token" value="<?= htmlspecialchars($_SESSION['csrf_token']) ?>">

    <input type="email" name="email" placeholder="Email" autocomplete="email" required><br><br>
    <input type="password" name="password" placeholder="Password" autocomplete="current-password" required><br><br>
    <button type="submit">Login</button>
  </form>
  <a href="register.php">Register</a>
</body>

</html>
