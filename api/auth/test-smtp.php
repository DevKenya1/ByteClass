<?php
require_once $_SERVER['DOCUMENT_ROOT'] . '/ByteClass/config/session.php';
require_once $_SERVER['DOCUMENT_ROOT'] . '/ByteClass/config/constants.php';
require_once $_SERVER['DOCUMENT_ROOT'] . '/ByteClass/config/database.php';
require_once $_SERVER['DOCUMENT_ROOT'] . '/ByteClass/helpers/response.php';

// Only accessible when logged in as admin
require_once $_SERVER['DOCUMENT_ROOT'] . '/ByteClass/config/session.php';
if (!isset($_SESSION['role']) || $_SESSION['role'] !== 'admin') {
    die(json_encode(['success' => false, 'message' => 'Unauthorized']));
}

require_once $_SERVER['DOCUMENT_ROOT'] . '/ByteClass/helpers/mailer.php';

$test_email = $_SESSION['email'];
$sent = send_email(
    $test_email,
    $_SESSION['full_name'],
    'ByteClass SMTP Test',
    email_template('SMTP Test Successful!', '
        <p style="color:#475569;font-size:15px;margin:0 0 16px">Hello <strong>' . htmlspecialchars($_SESSION['full_name']) . '</strong>,</p>
        <p style="color:#475569;font-size:15px;margin:0">Your SMTP configuration is working correctly. ByteClass can now send emails.</p>
    ')
);

if ($sent) {
    echo json_encode(['success' => true,  'message' => "Test email sent to $test_email. Check your inbox."]);
} else {
    echo json_encode(['success' => false, 'message' => 'Failed to send. Check SMTP settings and Gmail App Password.']);
}
