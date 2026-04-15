<?php
session_start();

if (!empty($_SESSION['user_id'])) {
  header('Location: dashboard.php');
  exit();
}

if (empty($_SESSION['csrf_token'])) {
  $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
}

$pageTitle = 'Login';
$error = $_GET['error'] ?? '';
$success = $_GET['success'] ?? '';

$errorMessages = [
  'missing_fields' => 'Email and password are required.',
  'invalid_email' => 'Please enter a valid email address.',
  'invalid_credentials' => 'Invalid email or password.',
  'invalid_csrf' => 'Your session expired. Please try again.',
  'method_not_allowed' => 'Invalid request method.',
  'db_error' => 'A database error occurred. Please try again.'
];

$successMessages = [
  'registered' => 'Registration successful. Please log in.',
  'logged_out' => 'You have been logged out.'
];

include 'includes/header.php';
?>
<div class="container">
  <h2>Login</h2>

  <?php if (isset($errorMessages[$error])): ?>
    <div id="feedback" style="display:block; color:red; background:#fff; padding:10px; margin:10px 0;">
      <?= htmlspecialchars($errorMessages[$error]) ?>
    </div>
  <?php elseif (isset($successMessages[$success])): ?>
    <div id="feedback" style="display:block; color:green; background:#fff; padding:10px; margin:10px 0;">
      <?= htmlspecialchars($successMessages[$success]) ?>
    </div>
  <?php endif; ?>

  <form action="actions/login.php" method="POST">
    <input type="hidden" name="csrf_token" value="<?= htmlspecialchars($_SESSION['csrf_token']) ?>">

    <label for="login-email">Email</label><br>
    <input type="email" id="login-email" name="email" autocomplete="email" required><br><br>

    <label for="login-password">Password</label><br>
    <input type="password" id="login-password" name="password" autocomplete="current-password" required><br><br>

    <button type="submit">Login</button>
  </form>

  <p><a href="register.php">Create a new account</a></p>
</div>
<?php include 'includes/footer.php'; ?>
