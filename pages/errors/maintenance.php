<?php
require_once $_SERVER['DOCUMENT_ROOT'] . '/ByteClass/config/constants.php';
require_once $_SERVER['DOCUMENT_ROOT'] . '/ByteClass/config/database.php';
http_response_code(503);
$message = 'ByteClass is undergoing scheduled maintenance. We will be back shortly.';
try {
    $db_m = Database::getInstance()->getConnection();
    $msg  = $db_m->query("SELECT setting_value FROM system_settings WHERE setting_key='maintenance_message'")->fetchColumn();
    if ($msg) $message = $msg;
} catch (Exception $e) {}
$page_title = 'Under Maintenance';
require_once $_SERVER['DOCUMENT_ROOT'] . '/ByteClass/includes/head.php';
?>
<div class="min-h-screen bg-gradient-to-br from-indigo-900 via-indigo-800 to-cyan-900 flex items-center justify-center px-4">
  <div class="text-center max-w-lg">
    <div class="w-20 h-20 bg-white bg-opacity-10 rounded-3xl flex items-center justify-center mx-auto mb-6">
      <i data-lucide="wrench" class="w-10 h-10 text-cyan-400"></i>
    </div>
    <h1 class="text-3xl font-bold text-white mb-3">
      <span class="text-cyan-400">Byte</span>Class is under maintenance
    </h1>
    <p class="text-indigo-200 text-lg mb-8 leading-relaxed"><?= htmlspecialchars($message) ?></p>
    <div class="bg-white bg-opacity-10 rounded-2xl px-6 py-4 inline-block">
      <p class="text-indigo-200 text-sm">We apologise for the inconvenience.</p>
      <p class="text-white font-semibold text-sm mt-1">Learn · Build · Grow</p>
    </div>
  </div>
</div>
<script src="https://cdn.tailwindcss.com"></script>
<script src="https://unpkg.com/lucide@latest/dist/umd/lucide.min.js"></script>
<script>lucide.createIcons();</script>
</body></html>
