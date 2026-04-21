<?php
require_once $_SERVER['DOCUMENT_ROOT'] . '/ByteClass/config/session.php';
require_once $_SERVER['DOCUMENT_ROOT'] . '/ByteClass/config/constants.php';
require_once $_SERVER['DOCUMENT_ROOT'] . '/ByteClass/config/database.php';
require_once $_SERVER['DOCUMENT_ROOT'] . '/ByteClass/helpers/jwt.php';

if (isset($_SESSION['token'])) {
    try {
        $db = Database::getInstance()->getConnection();
        $db->prepare("DELETE FROM user_sessions WHERE token = ?")->execute([$_SESSION['token']]);
        if (isset($_SESSION['user_id'])) {
            $db->prepare("INSERT INTO activity_logs (user_id, action, description, ip_address) VALUES (?,?,?,?)")
               ->execute([$_SESSION['user_id'], 'logout', 'User logged out', $_SERVER['REMOTE_ADDR'] ?? '']);
        }
    } catch (Exception $e) {}
}

session_destroy();
header('Location: ' . APP_URL . '/pages/auth/login.php');
exit;
