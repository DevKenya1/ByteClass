<?php
require_once $_SERVER['DOCUMENT_ROOT'] . '/ByteClass/config/constants.php';
require_once $_SERVER['DOCUMENT_ROOT'] . '/ByteClass/config/cors.php';
require_once $_SERVER['DOCUMENT_ROOT'] . '/ByteClass/config/database.php';
require_once $_SERVER['DOCUMENT_ROOT'] . '/ByteClass/helpers/response.php';

$action = sanitize($_GET['action'] ?? 'load');
$token  = sanitize($_GET['token']  ?? '');

if (!$token || strlen($token) < 16) respond_error('Invalid session.');

$db = Database::getInstance()->getConnection();

// Ensure table exists
$db->exec("CREATE TABLE IF NOT EXISTS public_chat_sessions (
    id INT AUTO_INCREMENT PRIMARY KEY,
    token VARCHAR(64) NOT NULL,
    message TEXT NOT NULL,
    is_admin TINYINT(1) DEFAULT 0,
    sender_name VARCHAR(100) DEFAULT 'Visitor',
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    INDEX (token)
)");

if ($action === 'send') {
    $input   = json_decode(file_get_contents('php://input'), true);
    $message = trim($input['message'] ?? '');
    if (!$message) respond_error('Message required.');
    if (strlen($message) > 500) respond_error('Message too long.');

    $db->prepare("INSERT INTO public_chat_sessions (token, message, is_admin, sender_name) VALUES (?,?,0,'Visitor')")
       ->execute([$token, $message]);

    // Notify admins
    $notif_stmt = $db->query("SELECT id FROM users WHERE role='admin'");
    foreach ($notif_stmt->fetchAll() as $a) {
        $db->prepare("INSERT INTO notifications (user_id,title,message,type) VALUES (?,?,?,?)")
           ->execute([$a['id'], 'New Public Chat Message', substr($message,0,80), 'support']);
    }

    respond_success('Sent.', ['message_id' => $db->lastInsertId()]);
}

if ($action === 'load') {
    $msgs = $db->prepare("SELECT message, is_admin, sender_name, created_at FROM public_chat_sessions WHERE token=? ORDER BY created_at ASC LIMIT 50");
    $msgs->execute([$token]);
    respond_success('Loaded.', ['messages' => $msgs->fetchAll()]);
}

if ($action === 'clear') {
    $db->prepare("DELETE FROM public_chat_sessions WHERE token=?")->execute([$token]);
    respond_success('Cleared.');
}

respond_error('Unknown action.');
