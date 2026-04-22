<?php
// Hardened session start with secure cookie params
if (session_status() === PHP_SESSION_NONE) {
    // Preserve existing params but enforce httponly and samesite
    $current = session_get_cookie_params();
    $secure = (!empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off');

    // Some PHP versions accept an array; fallback to positional for older PHP
    if (version_compare(PHP_VERSION, '7.3.0', '>=')) {
        session_set_cookie_params([
            'lifetime' => $current['lifetime'],
            'path' => $current['path'],
            'domain' => $current['domain'],
            'secure' => $secure,
            'httponly' => true,
            'samesite' => 'Lax',
        ]);
    } else {
        session_set_cookie_params($current['lifetime'], $current['path'], $current['domain'], $secure, true);
    }

    session_start();
}

if (!isset($_SESSION['user_id'])) {
    header("Location: index.php");
    exit();
}

function requireRole($role)
{
    $roles = is_array($role) ? $role : [$role];
    if (!in_array($_SESSION['role'] ?? '', $roles, true)) {
        $isAjax = strtolower($_SERVER['HTTP_X_REQUESTED_WITH'] ?? '') === 'xmlhttprequest'
               || stripos($_SERVER['HTTP_ACCEPT'] ?? '', 'application/json') !== false;
        
        if ($isAjax) {
            http_response_code(403);
            header('Content-Type: application/json');
            echo json_encode(['success' => false, 'message' => 'Unauthorized']);
            exit();
        }

        header("Location: index.php");
        exit();
    }
}
