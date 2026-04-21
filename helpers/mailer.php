<?php
use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;

require_once __DIR__ . '/../vendor/autoload.php';
require_once __DIR__ . '/../config/constants.php';
require_once __DIR__ . '/../config/database.php';

function get_smtp_config(): array {
    try {
        $db   = Database::getInstance()->getConnection();
        $stmt = $db->query("SELECT setting_key, setting_value FROM system_settings WHERE setting_key LIKE 'smtp_%' OR setting_key LIKE 'social_%' OR setting_key = 'platform_name'");
        $rows = $stmt->fetchAll();
        $config = [];
        foreach ($rows as $row) $config[$row['setting_key']] = $row['setting_value'];
        return $config;
    } catch (Exception $e) {
        return [];
    }
}

function make_mailer(): PHPMailer {
    $config = get_smtp_config();
    $mail   = new PHPMailer(true);
    $mail->isSMTP();
    $mail->Host       = $config['smtp_host']  ?? 'smtp.gmail.com';
    $mail->SMTPAuth   = true;
    $mail->Username   = $config['smtp_user']  ?? '';
    $mail->Password   = $config['smtp_pass']  ?? '';
    $mail->SMTPSecure = PHPMailer::ENCRYPTION_STARTTLS;
    $mail->Port       = (int)($config['smtp_port'] ?? 587);
    $mail->CharSet    = 'UTF-8';
    $mail->Timeout    = 10;
    $from_email = $config['smtp_from_email'] ?? ($config['smtp_user'] ?? 'noreply@byteclass.io');
    $from_name  = $config['smtp_from_name']  ?? 'ByteClass';
    $mail->setFrom($from_email, $from_name);
    return $mail;
}

function get_social_links(): array {
    $config = get_smtp_config();
    return [
        'website'   => $config['social_website']   ?? '',
        'whatsapp'  => $config['social_whatsapp']  ?? '',
        'telegram'  => $config['social_telegram']  ?? '',
        'twitter'   => $config['social_twitter']   ?? '',
        'facebook'  => $config['social_facebook']  ?? '',
        'instagram' => $config['social_instagram'] ?? '',
    ];
}

function email_social_footer(): string {
    $links  = get_social_links();
    $icons  = '';

    $socials = [
        'website'   => ['color'=>'#4F46E5', 'label'=>'Website',   'svg'=>'<svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><circle cx="12" cy="12" r="10"/><line x1="2" y1="12" x2="22" y2="12"/><path d="M12 2a15.3 15.3 0 0 1 4 10 15.3 15.3 0 0 1-4 10 15.3 15.3 0 0 1-4-10 15.3 15.3 0 0 1 4-10z"/></svg>'],
        'whatsapp'  => ['color'=>'#25D366', 'label'=>'WhatsApp',  'svg'=>'<svg width="20" height="20" viewBox="0 0 24 24" fill="currentColor"><path d="M17.472 14.382c-.297-.149-1.758-.867-2.03-.967-.273-.099-.471-.148-.67.15-.197.297-.767.966-.94 1.164-.173.199-.347.223-.644.075-.297-.15-1.255-.463-2.39-1.475-.883-.788-1.48-1.761-1.653-2.059-.173-.297-.018-.458.13-.606.134-.133.298-.347.446-.52.149-.174.198-.298.298-.497.099-.198.05-.371-.025-.52-.075-.149-.669-1.612-.916-2.207-.242-.579-.487-.5-.669-.51-.173-.008-.371-.01-.57-.01-.198 0-.52.074-.792.372-.272.297-1.04 1.016-1.04 2.479 0 1.462 1.065 2.875 1.213 3.074.149.198 2.096 3.2 5.077 4.487.709.306 1.262.489 1.694.625.712.227 1.36.195 1.871.118.571-.085 1.758-.719 2.006-1.413.248-.694.248-1.289.173-1.413-.074-.124-.272-.198-.57-.347z"/><path d="M12 0C5.373 0 0 5.373 0 12c0 2.117.554 4.103 1.523 5.824L0 24l6.341-1.499A11.947 11.947 0 0 0 12 24c6.627 0 12-5.373 12-12S18.627 0 12 0zm0 21.6a9.578 9.578 0 0 1-4.953-1.376l-.355-.211-3.664.865.93-3.568-.231-.367A9.578 9.578 0 0 1 2.4 12C2.4 6.698 6.698 2.4 12 2.4c5.302 0 9.6 4.298 9.6 9.6 0 5.302-4.298 9.6-9.6 9.6z"/></svg>'],
        'telegram'  => ['color'=>'#0088cc', 'label'=>'Telegram',  'svg'=>'<svg width="20" height="20" viewBox="0 0 24 24" fill="currentColor"><path d="M12 0C5.373 0 0 5.373 0 12s5.373 12 12 12 12-5.373 12-12S18.627 0 12 0zm5.562 8.248l-1.97 9.289c-.145.658-.537.818-1.084.508l-3-2.21-1.447 1.394c-.16.16-.295.295-.605.295l.213-3.053 5.56-5.023c.242-.213-.054-.333-.373-.12l-6.871 4.326-2.962-.924c-.643-.204-.657-.643.136-.953l11.57-4.461c.537-.194 1.006.131.833.932z"/></svg>'],
        'twitter'   => ['color'=>'#1DA1F2', 'label'=>'X / Twitter','svg'=>'<svg width="20" height="20" viewBox="0 0 24 24" fill="currentColor"><path d="M18.244 2.25h3.308l-7.227 8.26 8.502 11.24H16.17l-4.714-6.231-5.401 6.231H2.748l7.73-8.835L1.254 2.25H8.08l4.253 5.622zm-1.161 17.52h1.833L7.084 4.126H5.117z"/></svg>'],
    ];

    foreach ($socials as $key => $meta) {
        if (!empty($links[$key])) {
            $icons .= '<a href="' . htmlspecialchars($links[$key]) . '" style="display:inline-block;margin:0 6px;color:' . $meta['color'] . ';text-decoration:none" title="' . $meta['label'] . '">' . $meta['svg'] . '</a>';
        }
    }

    if (!$icons) return '';

    return '<div style="text-align:center;padding:20px 0 8px;border-top:1px solid #e2e8f0;margin-top:24px">' . $icons . '</div>';
}

function email_template(string $title, string $body_html, bool $show_social = true): string {
    $social_footer = $show_social ? email_social_footer() : '';
    return '<!DOCTYPE html>
<html>
<head><meta charset="UTF-8"><meta name="viewport" content="width=device-width,initial-scale=1"></head>
<body style="margin:0;padding:0;background:#f8fafc;font-family:Arial,sans-serif">
<table width="100%" cellpadding="0" cellspacing="0" style="background:#f8fafc;padding:40px 0">
<tr><td align="center">
<table width="600" cellpadding="0" cellspacing="0" style="background:#ffffff;border-radius:16px;overflow:hidden;box-shadow:0 4px 24px rgba(0,0,0,0.06)">
  <tr><td style="background:linear-gradient(135deg,#4F46E5,#06B6D4);padding:32px 40px;text-align:center">
    <h1 style="margin:0;color:#ffffff;font-size:26px;font-weight:700">
      <span style="color:#a5f3fc">Byte</span>Class
    </h1>
    <p style="margin:6px 0 0;color:#e0e7ff;font-size:13px">Learn · Build · Grow</p>
  </td></tr>
  <tr><td style="padding:36px 40px">
    <h2 style="margin:0 0 20px;color:#1e293b;font-size:20px;font-weight:700">' . $title . '</h2>
    ' . $body_html . '
    ' . $social_footer . '
  </td></tr>
  <tr><td style="background:#f8fafc;padding:16px 40px;text-align:center;border-top:1px solid #e2e8f0">
    <p style="margin:0;color:#94a3b8;font-size:12px">&copy; ' . date('Y') . ' ByteClass. All rights reserved. Nairobi, Kenya.</p>
  </td></tr>
</table>
</td></tr>
</table>
</body>
</html>';
}

function send_email(string $to, string $to_name, string $subject, string $html_body): bool {
    try {
        $mail = make_mailer();
        $mail->addAddress($to, $to_name);
        $mail->isHTML(true);
        $mail->Subject = $subject;
        $mail->Body    = $html_body;
        $mail->AltBody = strip_tags(str_replace(['<br>','<br/>','<p>','</p>','</tr>','</td>'], "\n", $html_body));
        $mail->send();

        // Log the email
        try {
            $db = Database::getInstance()->getConnection();
            $db->prepare("INSERT INTO activity_logs (user_id, action, description, ip_address) VALUES (NULL, 'email_sent', ?, ?)")
               ->execute(["Email sent to: $to — Subject: $subject", $_SERVER['REMOTE_ADDR'] ?? 'cron']);
        } catch (Exception $e) {}

        return true;
    } catch (Exception $e) {
        error_log("Mailer error: " . $e->getMessage());
        // Log the failure
        try {
            $db = Database::getInstance()->getConnection();
            $db->prepare("INSERT INTO activity_logs (user_id, action, description, ip_address) VALUES (NULL, 'email_failed', ?, ?)")
               ->execute(["Failed email to: $to — " . $e->getMessage(), $_SERVER['REMOTE_ADDR'] ?? 'cron']);
        } catch (Exception $ex) {}
        return false;
    }
}

function send_otp_email(string $to, string $name, string $otp): bool {
    $subject = 'Your ByteClass Verification Code — ' . $otp;
    $body    = email_template('Two-Factor Verification', '
        <p style="color:#475569;font-size:15px;line-height:1.6;margin:0 0 16px">Hello <strong style="color:#1e293b">' . htmlspecialchars($name) . '</strong>,</p>
        <p style="color:#475569;font-size:15px;line-height:1.6;margin:0 0 24px">Your one-time verification code is:</p>
        <div style="text-align:center;margin:0 0 28px">
          <div style="display:inline-block;background:#EEF2FF;border:2px dashed #4F46E5;border-radius:14px;padding:18px 48px">
            <span style="font-size:40px;font-weight:800;letter-spacing:10px;color:#4F46E5">' . $otp . '</span>
          </div>
        </div>
        <p style="color:#64748b;font-size:14px;margin:0 0 8px">⏱ This code expires in <strong>10 minutes</strong>.</p>
        <p style="color:#94a3b8;font-size:13px;margin:0">Do not share this code with anyone, including ByteClass staff.</p>
    ');
    return send_email($to, $name, $subject, $body);
}

function email_social_footer_inline(): string {
    $links = get_social_links();
    $icons = '';
    $socials = [
        'website'   => ['color'=>'#4F46E5', 'label'=>'Website',    'svg'=>'<svg width="22" height="22" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" style="vertical-align:middle"><circle cx="12" cy="12" r="10"/><line x1="2" y1="12" x2="22" y2="12"/><path d="M12 2a15.3 15.3 0 0 1 4 10 15.3 15.3 0 0 1-4 10 15.3 15.3 0 0 1-4-10 15.3 15.3 0 0 1 4-10z"/></svg>'],
        'whatsapp'  => ['color'=>'#25D366', 'label'=>'WhatsApp',   'svg'=>'<svg width="22" height="22" viewBox="0 0 24 24" fill="#25D366" style="vertical-align:middle"><path d="M17.472 14.382c-.297-.149-1.758-.867-2.03-.967-.273-.099-.471-.148-.67.15-.197.297-.767.966-.94 1.164-.173.199-.347.223-.644.075-.297-.15-1.255-.463-2.39-1.475-.883-.788-1.48-1.761-1.653-2.059-.173-.297-.018-.458.13-.606.134-.133.298-.347.446-.52.149-.174.198-.298.298-.497.099-.198.05-.371-.025-.52-.075-.149-.669-1.612-.916-2.207-.242-.579-.487-.5-.669-.51-.173-.008-.371-.01-.57-.01-.198 0-.52.074-.792.372-.272.297-1.04 1.016-1.04 2.479 0 1.462 1.065 2.875 1.213 3.074.149.198 2.096 3.2 5.077 4.487.709.306 1.262.489 1.694.625.712.227 1.36.195 1.871.118.571-.085 1.758-.719 2.006-1.413.248-.694.248-1.289.173-1.413-.074-.124-.272-.198-.57-.347z"/><path d="M12 0C5.373 0 0 5.373 0 12c0 2.117.554 4.103 1.523 5.824L0 24l6.341-1.499A11.947 11.947 0 0 0 12 24c6.627 0 12-5.373 12-12S18.627 0 12 0zm0 21.6a9.578 9.578 0 0 1-4.953-1.376l-.355-.211-3.664.865.93-3.568-.231-.367A9.578 9.578 0 0 1 2.4 12C2.4 6.698 6.698 2.4 12 2.4c5.302 0 9.6 4.298 9.6 9.6 0 5.302-4.298 9.6-9.6 9.6z"/></svg>'],
        'telegram'  => ['color'=>'#0088cc', 'label'=>'Telegram',   'svg'=>'<svg width="22" height="22" viewBox="0 0 24 24" fill="#0088cc" style="vertical-align:middle"><path d="M12 0C5.373 0 0 5.373 0 12s5.373 12 12 12 12-5.373 12-12S18.627 0 12 0zm5.562 8.248l-1.97 9.289c-.145.658-.537.818-1.084.508l-3-2.21-1.447 1.394c-.16.16-.295.295-.605.295l.213-3.053 5.56-5.023c.242-.213-.054-.333-.373-.12l-6.871 4.326-2.962-.924c-.643-.204-.657-.643.136-.953l11.57-4.461c.537-.194 1.006.131.833.932z"/></svg>'],
        'twitter'   => ['color'=>'#000000', 'label'=>'X',          'svg'=>'<svg width="22" height="22" viewBox="0 0 24 24" fill="#000000" style="vertical-align:middle"><path d="M18.244 2.25h3.308l-7.227 8.26 8.502 11.24H16.17l-4.714-6.231-5.401 6.231H2.748l7.73-8.835L1.254 2.25H8.08l4.253 5.622zm-1.161 17.52h1.833L7.084 4.126H5.117z"/></svg>'],
        'facebook'  => ['color'=>'#1877F2', 'label'=>'Facebook',   'svg'=>'<svg width="22" height="22" viewBox="0 0 24 24" fill="#1877F2" style="vertical-align:middle"><path d="M24 12.073c0-6.627-5.373-12-12-12s-12 5.373-12 12c0 5.99 4.388 10.954 10.125 11.854v-8.385H7.078v-3.47h3.047V9.43c0-3.007 1.792-4.669 4.533-4.669 1.312 0 2.686.235 2.686.235v2.953H15.83c-1.491 0-1.956.925-1.956 1.874v2.25h3.328l-.532 3.47h-2.796v8.385C19.612 23.027 24 18.062 24 12.073z"/></svg>'],
        'instagram' => ['color'=>'#E4405F', 'label'=>'Instagram',  'svg'=>'<svg width="22" height="22" viewBox="0 0 24 24" fill="#E4405F" style="vertical-align:middle"><path d="M12 2.163c3.204 0 3.584.012 4.85.07 3.252.148 4.771 1.691 4.919 4.919.058 1.265.069 1.645.069 4.849 0 3.205-.012 3.584-.069 4.849-.149 3.225-1.664 4.771-4.919 4.919-1.266.058-1.644.07-4.85.07-3.204 0-3.584-.012-4.849-.07-3.26-.149-4.771-1.699-4.919-4.92-.058-1.265-.07-1.644-.07-4.849 0-3.204.013-3.583.07-4.849.149-3.227 1.664-4.771 4.919-4.919 1.266-.057 1.645-.069 4.849-.069zM12 0C8.741 0 8.333.014 7.053.072 2.695.272.273 2.69.073 7.052.014 8.333 0 8.741 0 12c0 3.259.014 3.668.072 4.948.2 4.358 2.618 6.78 6.98 6.98C8.333 23.986 8.741 24 12 24c3.259 0 3.668-.014 4.948-.072 4.354-.2 6.782-2.618 6.979-6.98.059-1.28.073-1.689.073-4.948 0-3.259-.014-3.667-.072-4.947-.196-4.354-2.617-6.78-6.979-6.98C15.668.014 15.259 0 12 0zm0 5.838a6.162 6.162 0 1 0 0 12.324 6.162 6.162 0 0 0 0-12.324zM12 16a4 4 0 1 1 0-8 4 4 0 0 1 0 8zm6.406-11.845a1.44 1.44 0 1 0 0 2.881 1.44 1.44 0 0 0 0-2.881z"/></svg>'],
    ];
    foreach ($socials as $key => $meta) {
        if (!empty($links[$key])) {
            $icons .= '<a href="' . htmlspecialchars($links[$key]) . '" style="display:inline-block;margin:0 8px;text-decoration:none" title="' . $meta['label'] . '">' . $meta['svg'] . '</a>';
        }
    }
    return $icons ?: '';
}
function send_temp_password_email(string $to, string $name, string $temp_password): bool {
    $login_url   = APP_URL . '/pages/auth/login.php';
    $config      = get_smtp_config();
    $website_url = $config['social_website'] ?? APP_URL;

    $subject = 'Welcome to ByteClass — Your Lecturer Account is Ready';
    $body    = email_template('Your Lecturer Account Has Been Created!', '
        <p style="color:#475569;font-size:15px;line-height:1.6;margin:0 0 16px">Hello <strong style="color:#1e293b">' . htmlspecialchars($name) . '</strong>,</p>
        <p style="color:#475569;font-size:15px;line-height:1.6;margin:0 0 16px">
          Your lecturer account on <strong>ByteClass</strong> has been created successfully by an administrator.
          You can now log in and start creating content for your students.
        </p>
        <div style="background:#f8fafc;border:1px solid #e2e8f0;border-radius:12px;padding:20px 24px;margin:0 0 24px">
          <p style="margin:0 0 4px;color:#64748b;font-size:12px;font-weight:700;text-transform:uppercase;letter-spacing:1px">Your Login Details</p>
          <table cellpadding="0" cellspacing="0" style="width:100%;margin-top:12px">
            <tr>
              <td style="color:#64748b;font-size:14px;padding:6px 0;width:120px">Email address</td>
              <td style="color:#1e293b;font-size:14px;font-weight:600;padding:6px 0">' . htmlspecialchars($to) . '</td>
            </tr>
            <tr>
              <td style="color:#64748b;font-size:14px;padding:6px 0">Temporary password</td>
              <td style="padding:6px 0"><span style="font-family:monospace;background:#EEF2FF;color:#4F46E5;font-weight:700;font-size:15px;padding:4px 10px;border-radius:6px">' . htmlspecialchars($temp_password) . '</span></td>
            </tr>
          </table>
        </div>
        <div style="background:#FFF7ED;border:1px solid #FED7AA;border-radius:10px;padding:12px 16px;margin:0 0 28px">
          <p style="margin:0;color:#92400E;font-size:13px">⚠️ Please change your password immediately after your first login for security.</p>
        </div>
        <table cellpadding="0" cellspacing="0" style="margin:0 auto">
          <tr>
            <td style="padding:0 8px">
              <a href="' . $login_url . '" style="display:inline-block;background:#4F46E5;color:#ffffff;padding:14px 28px;border-radius:10px;font-size:15px;font-weight:600;text-decoration:none">
                Login to ByteClass
              </a>
            </td>
            <td style="padding:0 8px">
              <a href="' . $website_url . '" style="display:inline-block;background:#f1f5f9;color:#475569;padding:14px 28px;border-radius:10px;font-size:15px;font-weight:600;text-decoration:none;border:1px solid #e2e8f0">
                Visit Website
              </a>
            </td>
          </tr>
       </table>
        <div style="text-align:center;margin-top:28px;padding-top:20px;border-top:1px solid #e2e8f0">
          <p style="color:#94a3b8;font-size:12px;margin:0 0 12px">Connect with us</p>
' . email_social_footer_inline() . '
        </div>
    ');
    return send_email($to, $name, $subject, $body);
}

function send_email_verification(string $to, string $name, string $token): bool {
    $verify_url = APP_URL . '/pages/auth/verify-email.php?token=' . $token;
    $subject    = 'Please verify your ByteClass email address';
    $body       = email_template('Verify Your Email Address', '
        <p style="color:#475569;font-size:15px;line-height:1.6;margin:0 0 16px">Hello <strong style="color:#1e293b">' . htmlspecialchars($name) . '</strong>,</p>
        <p style="color:#475569;font-size:15px;line-height:1.6;margin:0 0 20px">
          Thank you for registering on ByteClass! One more step — please verify your email address to activate your account.
        </p>
        <div style="text-align:center;margin:0 0 28px">
          <a href="' . $verify_url . '" style="display:inline-block;background:#4F46E5;color:#ffffff;padding:14px 32px;border-radius:10px;font-size:15px;font-weight:600;text-decoration:none">
            Verify My Email Address
          </a>
        </div>
        <p style="color:#94a3b8;font-size:13px;text-align:center;margin:0">This link expires in 24 hours. If you did not register on ByteClass, please ignore this email.</p>
    ');
    return send_email($to, $name, $subject, $body);
}

function send_password_reset_email(string $to, string $name, string $token): bool {
    $reset_url = APP_URL . '/pages/auth/reset-password.php?token=' . $token;
    $subject   = 'Reset your ByteClass password';
    $body      = email_template('Password Reset Request', '
        <p style="color:#475569;font-size:15px;line-height:1.6;margin:0 0 16px">Hello <strong style="color:#1e293b">' . htmlspecialchars($name) . '</strong>,</p>
        <p style="color:#475569;font-size:15px;line-height:1.6;margin:0 0 20px">
          We received a request to reset your ByteClass password. Click the button below to choose a new password.
        </p>
        <div style="text-align:center;margin:0 0 28px">
          <a href="' . $reset_url . '" style="display:inline-block;background:#4F46E5;color:#ffffff;padding:14px 32px;border-radius:10px;font-size:15px;font-weight:600;text-decoration:none">
            Reset My Password
          </a>
        </div>
        <p style="color:#94a3b8;font-size:13px;text-align:center;margin:0">This link expires in 24 hours. If you did not request a password reset, please ignore this email.</p>
    ');
    return send_email($to, $name, $subject, $body);
}
