<?php
require_once __DIR__ . '/../config/database.php';
require_once __DIR__ . '/../config/constants.php';
require_once __DIR__ . '/../helpers/response.php';
require_once __DIR__ . '/../helpers/jwt.php';

function authenticate(): array {
    $token = get_bearer_token();
    if (!$token) respond_error('Unauthorized. No token provided.', 401);

    $payload = jwt_decode_token($token);
    if (!$payload) respond_error('Unauthorized. Invalid or expired token.', 401);

    $db   = Database::getInstance()->getConnection();
    $stmt = $db->prepare("SELECT * FROM users WHERE id = ? AND status = 'active' LIMIT 1");
    $stmt->execute([$payload['user_id']]);
    $user = $stmt->fetch();

    if (!$user) respond_error('Unauthorized. User not found.', 401);

    $sess = $db->prepare("SELECT * FROM user_sessions WHERE user_id = ? AND token = ? LIMIT 1");
    $sess->execute([$user['id'], $token]);
    $session = $sess->fetch();

    if (!$session) respond_error('Session expired. Please login again.', 401);

    $inactive = (time() - strtotime($session['last_active'])) / 60;
    if ($inactive > AUTO_LOGOUT_MINUTES) {
        $db->prepare("DELETE FROM user_sessions WHERE id = ?")->execute([$session['id']]);
        respond_error('Session timed out. Please login again.', 401);
    }

    $db->prepare("UPDATE user_sessions SET last_active = NOW() WHERE id = ?")->execute([$session['id']]);
    return $user;
}

function require_role(array $user, string ...$roles): void {
    if (!in_array($user['role'], $roles)) {
        respond_error('Forbidden. Insufficient permissions.', 403);
    }
}
