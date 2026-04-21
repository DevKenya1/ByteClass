<?php
require_once $_SERVER['DOCUMENT_ROOT'] . '/ByteClass/config/session.php';
require_once $_SERVER['DOCUMENT_ROOT'] . '/ByteClass/config/cors.php';
require_once $_SERVER['DOCUMENT_ROOT'] . '/ByteClass/config/database.php';
require_once $_SERVER['DOCUMENT_ROOT'] . '/ByteClass/helpers/response.php';

if (empty($_SESSION['user_id'])) respond_error('Unauthorized', 401);

$input   = json_decode(file_get_contents('php://input'), true);
$message = trim($input['message'] ?? '');

if (!$message) respond_error('Message is required.');
if (strlen($message) > 1000) respond_error('Message too long.');

$db = Database::getInstance()->getConnection();

// Check AI is enabled
$enabled = $db->query("SELECT setting_value FROM system_settings WHERE setting_key='learnpulse_enabled'")->fetchColumn();
if (!$enabled || $enabled === '0') respond_error('AI tutor is not enabled.', 403);

// Get provider + key
$provider = $db->query("SELECT setting_value FROM system_settings WHERE setting_key='learnpulse_provider'")->fetchColumn() ?: 'gemini';
$api_key  = $db->query("SELECT setting_value FROM system_settings WHERE setting_key='gemini_api_key'")->fetchColumn();

if (!$api_key) respond_error('AI API key not configured. Please contact the administrator.', 503);

// System prompt
$system_prompt = "You are LearnPulse, a helpful AI tutor for ByteClass — an online learning platform focused on technology education. You help students understand course content, debug code, explain concepts clearly, and stay motivated. Keep responses concise, friendly, and educational. If asked about pricing, enrollment, or platform features, direct the student to speak with an administrator.";

$reply = '';

if ($provider === 'gemini') {
    $url  = "https://generativelanguage.googleapis.com/v1beta/models/gemini-1.5-flash:generateContent?key=$api_key";
    $body = json_encode([
        'contents'        => [['parts' => [['text' => $message]], 'role' => 'user']],
        'systemInstruction' => ['parts' => [['text' => $system_prompt]]],
        'generationConfig' => ['maxOutputTokens' => 512, 'temperature' => 0.7],
    ]);
    $ch = curl_init($url);
    curl_setopt_array($ch, [
        CURLOPT_RETURNTRANSFER => true,
        CURLOPT_POST           => true,
        CURLOPT_POSTFIELDS     => $body,
        CURLOPT_HTTPHEADER     => ['Content-Type: application/json'],
        CURLOPT_TIMEOUT        => 15,
    ]);
    $res  = curl_exec($ch);
    $code = curl_getinfo($ch, CURLINFO_HTTP_CODE);
    curl_close($ch);

    if ($code === 200) {
        $data  = json_decode($res, true);
        $reply = $data['candidates'][0]['content']['parts'][0]['text'] ?? 'Sorry, I could not generate a response.';
    } else {
        $err = json_decode($res, true);
        respond_error('AI service error: ' . ($err['error']['message'] ?? 'Unknown error'), 502);
    }

} elseif ($provider === 'grok') {
    $url  = "https://api.x.ai/v1/chat/completions";
    $body = json_encode([
        'model'    => 'grok-beta',
        'messages' => [
            ['role' => 'system', 'content' => $system_prompt],
            ['role' => 'user',   'content' => $message],
        ],
        'max_tokens'  => 512,
        'temperature' => 0.7,
    ]);
    $ch = curl_init($url);
    curl_setopt_array($ch, [
        CURLOPT_RETURNTRANSFER => true,
        CURLOPT_POST           => true,
        CURLOPT_POSTFIELDS     => $body,
        CURLOPT_HTTPHEADER     => ["Authorization: Bearer $api_key", 'Content-Type: application/json'],
        CURLOPT_TIMEOUT        => 15,
    ]);
    $res  = curl_exec($ch);
    $code = curl_getinfo($ch, CURLINFO_HTTP_CODE);
    curl_close($ch);

    if ($code === 200) {
        $data  = json_decode($res, true);
        $reply = $data['choices'][0]['message']['content'] ?? 'Sorry, I could not generate a response.';
    } else {
        $err = json_decode($res, true);
        respond_error('AI service error: ' . ($err['error']['message'] ?? 'Unknown error'), 502);
    }
} else {
    respond_error('Unknown AI provider configured.', 500);
}

// Log usage
$db->prepare("INSERT INTO activity_logs (user_id,action,description,ip_address) VALUES (?,?,?,?)")
   ->execute([$_SESSION['user_id'], 'ai_query', 'LearnPulse query: ' . substr($message,0,80), $_SERVER['REMOTE_ADDR']??'']);

respond_success('AI response generated.', ['reply' => $reply]);

