<?php
require_once $_SERVER['DOCUMENT_ROOT'] . '/ByteClass/config/session.php';
require_once $_SERVER['DOCUMENT_ROOT'] . '/ByteClass/config/constants.php';
require_once $_SERVER['DOCUMENT_ROOT'] . '/ByteClass/config/database.php';
require_once $_SERVER['DOCUMENT_ROOT'] . '/ByteClass/helpers/response.php';

// Not logged in at all
if (empty($_SESSION['user_id']) || empty($_SESSION['role'])) {
    header('Location: ' . APP_URL . '/pages/auth/login.php');
    exit;
}

// Verify session is still valid in DB
try {
    $db_auth = Database::getInstance()->getConnection();
    $verify  = $db_auth->prepare("
        SELECT u.id, u.role, u.status FROM users u
        JOIN user_sessions us ON us.user_id = u.id
        WHERE u.id = ? AND us.token = ? AND u.status = 'active'
        LIMIT 1
    ");
    $verify->execute([$_SESSION['user_id'], $_SESSION['token'] ?? '']);
    $db_user = $verify->fetch();

    if (!$db_user) {
        // Session invalid or user deactivated — clear and redirect
        session_destroy();
        header('Location: ' . APP_URL . '/pages/auth/login.php');
        exit;
    }

    // Always sync role from DB (prevents role tampering)
    $_SESSION['role'] = $db_user['role'];

} catch (Exception $e) {
    // DB error — still allow if session exists
}

// Role check
if (isset($required_role) && $_SESSION['role'] !== $required_role) {
    // Redirect to correct dashboard instead of 404
    $role = $_SESSION['role'];
    if ($role === 'admin')        { header('Location: ' . APP_URL . '/admin/dashboard.php');    exit; }
    elseif ($role === 'lecturer') { header('Location: ' . APP_URL . '/lecturer/dashboard.php'); exit; }
    else                          { header('Location: ' . APP_URL . '/student/dashboard.php');  exit; }
}
