<?php
require_once __DIR__ . '/../config/database.php';
require_once __DIR__ . '/../config/constants.php';
require_once __DIR__ . '/../helpers/response.php';

function check_login_rate(string $email): void {
    $db   = Database::getInstance()->getConnection();
    $stmt = $db->prepare("SELECT status FROM users WHERE email = ? LIMIT 1");
    $stmt->execute([$email]);
    $user = $stmt->fetch();
    if ($user && $user['status'] === 'locked') {
        respond_error('Account is locked due to too many failed attempts. Contact admin.', 423);
    }
}
function increment_login_attempts(int $user_id): void {
    $db = Database::getInstance()->getConnection();
    $db->prepare("UPDATE users SET login_attempts = login_attempts + 1 WHERE id = ?")->execute([$user_id]);
    $row = $db->prepare("SELECT login_attempts FROM users WHERE id = ?");
    $row->execute([$user_id]);
    if ((int)$row->fetchColumn() >= MAX_LOGIN_ATTEMPTS) {
        $db->prepare("UPDATE users SET status = 'locked', locked_at = NOW() WHERE id = ?")->execute([$user_id]);
    }
}
function reset_login_attempts(int $user_id): void {
    $db = Database::getInstance()->getConnection();
    $db->prepare("UPDATE users SET login_attempts = 0, locked_at = NULL WHERE id = ?")->execute([$user_id]);
}
