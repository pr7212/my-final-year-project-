<?php
session_start();

if (!isset($_SESSION['user_id'])) {
    header("Location: index.php");
    exit();
}




function requireRole($role)
{
    $roles = is_array($role) ? $role : [$role];
    if (!in_array($_SESSION['role'] ?? '', $roles, true)) {
        header("Location: index.php");
        exit();
    }
}
