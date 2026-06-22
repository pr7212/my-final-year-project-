<?php

/**
 * register.php
 *
 * Registration page for the Garbage Management and Tracking System (GMTS).
 * Handles new user account creation with CSRF protection and role-based
 * redirection for already-authenticated users.
 *
 * Authenticated users are redirected to their role-specific dashboard
 * before the page renders, preventing unnecessary access to the form.
 */

session_start();

// -----------------------------------------------------------------------
// Role-based redirect for authenticated users
// If a valid session already exists, send the user to their dashboard.
// This prevents logged-in users from seeing or submitting the register form.
// -----------------------------------------------------------------------
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
  // Halt execution after redirect regardless of whether a matching role was found.
  exit();
}

// -----------------------------------------------------------------------
// CSRF token initialisation
// A token is generated once per session and embedded in the form as a
// hidden field. The action handler (register_user.php) validates this
// token to prevent cross-site request forgery attacks.
// -----------------------------------------------------------------------
if (empty($_SESSION['csrf_token'])) {
  $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
}

// -----------------------------------------------------------------------
// Page metadata and feedback state
// Error/success codes are passed back via query string after the form
// action redirects, keeping feedback logic out of the action script.
// -----------------------------------------------------------------------
$pageTitle = 'Register';
$error = $_GET['error'] ?? '';
$success = $_GET['success'] ?? '';

// Human-readable messages keyed by the error codes returned by register_user.php.
// Centralising them here keeps the UI copy in one place and out of the action script.
$errorMessages = [
  'missing_fields'   => 'Please fill in all required fields.',
  'invalid_name'     => 'Your name must be between 2 and 100 characters.',
  'invalid_email'    => 'Please enter a valid email address.',
  'weak_password'    => 'Your password must be at least 8 characters long.',
  'password_mismatch' => 'The password confirmation does not match.',
  'email_exists'     => 'That email address is already registered.',
  'db_error'         => 'A database error occurred. Please try again.',
  'failed'           => 'Registration failed. Please try again.'
];

$successMessages = [
  'registered' => 'Registration successful. You can now log in.'
];

include 'includes/header.php';
?>
<div class="container">
  <h2>Create Account</h2>

  <?php
  /*
   * Feedback banner
   * Only one banner is shown at a time: errors take priority over success.
   * Output is escaped with htmlspecialchars() as a defence-in-depth measure,
   * even though the messages are defined server-side, not from user input.
   */
  ?>
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
    <?php /* CSRF token — must match $_SESSION['csrf_token'] in the action handler */ ?>
    <input type="hidden" name="csrf_token" value="<?= htmlspecialchars($_SESSION['csrf_token']) ?>">

    <label for="register-name">Full Name</label><br>
    <?php /* maxlength mirrors the server-side validation ceiling (100 chars) */ ?>
    <input type="text" id="register-name" name="name" maxlength="100" autocomplete="name" required><br><br>

    <label for="register-email">Email</label><br>
    <input type="email" id="register-email" name="email" maxlength="100" autocomplete="email" required><br><br>

    <label for="register-password">Password</label><br>
    <?php /* minlength provides a first-pass client-side guard matching the 8-char server rule */ ?>
    <input type="password" id="register-password" name="password" minlength="8" autocomplete="new-password" required><br><br>

    <label for="register-confirm-password">Confirm Password</label><br>
    <?php /* Separate field so the action handler can compare both values server-side */ ?>
    <input type="password" id="register-confirm-password" name="confirm_password" minlength="8" autocomplete="new-password" required><br><br>

    <button type="submit">Register</button>
  </form>

  <?php /* Redirects existing users to the login page rather than leaving them on a dead end */ ?>
  <p><a href="index.php">Already have an account? Log in</a></p>
</div>
<?php include 'includes/footer.php'; ?>
