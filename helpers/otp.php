<?php
require_once __DIR__ . '/../config/database.php';
require_once __DIR__ . '/../config/constants.php';

/**
 * Generate a 6-digit OTP, store it in DB, return the code
 */
function generate_and_store_otp(int $user_id, string $purpose = '2fa'): string {
    $db    = Database::getInstance()->getConnection();
    $otp   = str_pad((string)random_int(0, 999999), 6, '0', STR_PAD_LEFT);
    $hash  = password_hash($otp, PASSWORD_BCRYPT, ['cost' => 10]);
    $exp   = date('Y-m-d H:i:s', strtotime('+10 minutes'));

    // Delete any existing OTP for this user+purpose
    $db->prepare("DELETE FROM otp_codes WHERE user_id=? AND purpose=?")->execute([$user_id, $purpose]);

    // Insert new
    $db->prepare("INSERT INTO otp_codes (user_id, code_hash, purpose, expires_at) VALUES (?,?,?,?)")
       ->execute([$user_id, $hash, $purpose, $exp]);

    return $otp;
}

/**
 * Verify an OTP code — returns true and deletes on success, false on failure/expired
 */
function verify_otp(int $user_id, string $code, string $purpose = '2fa'): bool {
    $db   = Database::getInstance()->getConnection();
    $stmt = $db->prepare("SELECT id, code_hash, expires_at FROM otp_codes WHERE user_id=? AND purpose=? ORDER BY created_at DESC LIMIT 1");
    $stmt->execute([$user_id, $purpose]);
    $row  = $stmt->fetch();

    if (!$row) return false;
    if (strtotime($row['expires_at']) < time()) {
        $db->prepare("DELETE FROM otp_codes WHERE id=?")->execute([$row['id']]);
        return false;
    }
    if (!password_verify($code, $row['code_hash'])) return false;

    // Valid — delete it (one-time use)
    $db->prepare("DELETE FROM otp_codes WHERE id=?")->execute([$row['id']]);
    return true;
}

/**
 * Check if 2FA should be triggered for this user/device
 * Returns false if: 2FA disabled in settings, SMTP not configured, or user just verified recently
 */
function should_trigger_2fa(int $user_id, string $device_type = 'laptop'): bool {
    try {
        $db = Database::getInstance()->getConnection();

        // Check system setting
        $enabled = $db->query("SELECT setting_value FROM system_settings WHERE setting_key='two_fa_enabled'")->fetchColumn();
        if ($enabled !== '1') return false;

        // Check SMTP is configured
        $smtp_host = $db->query("SELECT setting_value FROM system_settings WHERE setting_key='smtp_host'")->fetchColumn();
        $smtp_pass = $db->query("SELECT setting_value FROM system_settings WHERE setting_key='smtp_pass'")->fetchColumn();
        if (empty($smtp_host) || empty($smtp_pass)) return false;

        return true;
    } catch (Exception $e) {
        return false;
    }
}

/**
 * Record that a 2FA code was sent (for rate-limiting)
 */
function record_2fa_sent(int $user_id): void {
    try {
        $db = Database::getInstance()->getConnection();
        $db->prepare("INSERT INTO activity_logs (user_id, action, description, ip_address) VALUES (?,?,?,?)")
           ->execute([$user_id, '2fa_sent', '2FA OTP sent', $_SERVER['REMOTE_ADDR'] ?? '']);
    } catch (Exception $e) {}
}
