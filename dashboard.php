<?php
include 'includes/auth.php';

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
  default:
    session_destroy();
    header('Location: index.php');
    break;
}
exit();
