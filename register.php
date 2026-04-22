<?php
session_start();

if (!empty($_SESSION['user_id'])) {
  switch ($_SESSION['role']) {
    case 'admin':
      header('Location: admin.php');
      break;
    case 'resident':
      header('Location: resident.php');
      break;
    case 'collector':
      header('Location: collector.php');
      break;
    case 'officer':
      header('Location: officer.php');
      break;
  }
  exit();
}

if (empty($_SESSION['csrf_token'])) {
  $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
}

$pageTitle = 'Register';
$error = $_GET['error'] ?? '';
$success = $_GET['success'] ?? '';

$errorMessages = [
  'missing_fields' => 'Please fill in all required fields.',
  'invalid_name' => 'Your name must be between 2 and 100 characters.',
  'invalid_email' => 'Please enter a valid email address.',
  'weak_password' => 'Your password must be at least 8 characters long.',
  'password_mismatch' => 'The password confirmation does not match.',
  'email_exists' => 'That email address is already registered.',
  'db_error' => 'A database error occurred. Please try again.',
  'failed' => 'Registration failed. Please try again.'
];

$successMessages = [
  'registered' => 'Registration successful. You can now log in.'
];

include 'includes/header.php';
?>
<div class="container">
  <h2>Create Account</h2>

  <?php if (isset($errorMessages[$error])): ?>
    <div class="alert alert-error">
      <?= htmlspecialchars($errorMessages[$error]) ?>
    </div>
  <?php elseif (isset($successMessages[$success])): ?>
    <div class="alert alert-success">
      <?= htmlspecialchars($successMessages[$success]) ?>
    </div>
  <?php endif; ?>

  <form action="actions/register_user.php" method="POST">
    <input type="hidden" name="csrf_token" value="<?= htmlspecialchars($_SESSION['csrf_token']) ?>">

    <label for="register-name">Full Name</label><br>
    <input type="text" id="register-name" name="name" maxlength="100" autocomplete="name" required><br><br>

    <label for="register-email">Email</label><br>
    <input type="email" id="register-email" name="email" maxlength="100" autocomplete="email" required><br><br>

    <label for="register-password">Password</label><br>
    <input type="password" id="register-password" name="password" minlength="8" autocomplete="new-password" required><br><br>

    <label for="register-confirm-password">Confirm Password</label><br>
    <input type="password" id="register-confirm-password" name="confirm_password" minlength="8" autocomplete="new-password" required><br><br>

    <button type="submit">Register</button>
  </form>

  <p><a href="index.php">Already have an account? Log in</a></p>
</div>
<?php include 'includes/footer.php'; ?>
