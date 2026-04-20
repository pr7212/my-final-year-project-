<?php
session_start();

if (!isset($_SESSION['user_id'])) {
    header("Location: index.php");
    exit();
}

// Auto-redirect to role dashboard
$role = $_SESSION['role'] ?? 'guest';
$maps = [
    'admin' => 'admin.php',
    'resident' => 'resident.php',
    'collector' => 'collector.php',
    'officer' => 'officer.php'
];
$dashboard = $maps[$role] ?? 'dashboard.php';
$current_page = basename($_SERVER['PHP_SELF']);
if ($current_page !== basename($dashboard)) {
    header("Location: $dashboard");
    exit();
}

function requireRole($role)
{
    if (!isset($_SESSION['role']) || $_SESSION['role'] !== $role) {
        header("Location: index.php");
        exit();
    }
}
