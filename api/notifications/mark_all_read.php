<?php
require_once $_SERVER['DOCUMENT_ROOT'] . '/ByteClass/config/session.php';
require_once $_SERVER['DOCUMENT_ROOT'] . '/ByteClass/config/cors.php';
require_once $_SERVER['DOCUMENT_ROOT'] . '/ByteClass/config/database.php';
require_once $_SERVER['DOCUMENT_ROOT'] . '/ByteClass/helpers/response.php';

if (empty($_SESSION['user_id'])) {
    respond_error('Unauthorized', 401);
}

$db = Database::getInstance()->getConnection();
$db->prepare("UPDATE notifications SET is_read = 1 WHERE user_id = ?")
   ->execute([$_SESSION['user_id']]);

respond_success('All notifications marked as read.');
