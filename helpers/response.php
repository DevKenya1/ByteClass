<?php
function respond(bool $success, string $message, array $data = [], int $code = 200): void {
    http_response_code($code);
    echo json_encode(['success' => $success, 'message' => $message, 'data' => $data], JSON_UNESCAPED_UNICODE);
    exit;
}
function respond_success(string $message, array $data = [], int $code = 200): void {
    respond(true, $message, $data, $code);
}
function respond_error(string $message, int $code = 400, array $data = []): void {
    respond(false, $message, $data, $code);
}
function get_json_body(): array {
    $raw  = file_get_contents('php://input');
    $data = json_decode($raw, true);
    return is_array($data) ? $data : [];
}
function sanitize(string $value): string {
    return htmlspecialchars(strip_tags(trim($value)), ENT_QUOTES, 'UTF-8');
}
function generate_token(int $length = 64): string {
    return bin2hex(random_bytes($length / 2));
}
function generate_otp(): string {
    return str_pad((string) random_int(0, 999999), 6, '0', STR_PAD_LEFT);
}
function generate_uid(string $prefix = 'BC'): string {
    return $prefix . '-' . date('Y') . '-' . strtoupper(substr(bin2hex(random_bytes(4)), 0, 8));
}
function redirect(string $url): void {
    header("Location: " . APP_URL . $url);
    exit;
}
function is_logged_in(): bool {
    return isset($_SESSION['user_id']) && isset($_SESSION['token']);
}
function current_user(): ?array {
    if (!is_logged_in()) return null;
    return [
        'id'        => $_SESSION['user_id'],
        'full_name' => $_SESSION['full_name'],
        'email'     => $_SESSION['email'],
        'role'      => $_SESSION['role'],
        'photo'     => $_SESSION['photo'] ?? null,
    ];
}
function require_login(string $role = ''): void {
    if (!is_logged_in()) {
        redirect('/pages/auth/login.php');
    }
    if ($role && $_SESSION['role'] !== $role) {
        redirect('/pages/errors/404.php');
    }
}
function time_ago(string $datetime): string {
    $time = strtotime($datetime);
    $diff = time() - $time;
    if ($diff < 60)     return 'just now';
    if ($diff < 3600)   return floor($diff/60) . 'm ago';
    if ($diff < 86400)  return floor($diff/3600) . 'h ago';
    return floor($diff/86400) . 'd ago';
}
