<?php
require_once __DIR__ . '/../vendor/autoload.php';
require_once __DIR__ . '/../config/constants.php';
use Firebase\JWT\JWT;
use Firebase\JWT\Key;

function jwt_encode_token(array $payload): string {
    $payload['iat'] = time();
    $payload['exp'] = time() + JWT_EXPIRY;
    return JWT::encode($payload, JWT_SECRET, 'HS256');
}
function jwt_decode_token(string $token): ?array {
    try {
        $decoded = JWT::decode($token, new Key(JWT_SECRET, 'HS256'));
        return (array) $decoded;
    } catch (Exception $e) {
        return null;
    }
}
function get_bearer_token(): ?string {
    $headers = getallheaders();
    $auth    = $headers['Authorization'] ?? $headers['authorization'] ?? '';
    if (str_starts_with($auth, 'Bearer ')) return substr($auth, 7);
    return null;
}
