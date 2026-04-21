<?php
require_once $_SERVER['DOCUMENT_ROOT'] . '/ByteClass/config/constants.php';
http_response_code(500);
$page_title = '500 — Server Error';
require_once $_SERVER['DOCUMENT_ROOT'] . '/ByteClass/includes/head.php';
?>
<div class="min-h-screen bg-gradient-to-br from-red-50 via-white to-orange-50 flex items-center justify-center px-4">
  <div class="text-center max-w-md">
    <div class="text-8xl font-black text-red-500 mb-4 leading-none">500</div>
    <h1 class="text-2xl font-bold text-gray-900 mb-3">Internal server error</h1>
    <p class="text-gray-500 mb-8">Something went wrong on our end. We are working to fix it. Please try again shortly.</p>
    <div class="flex gap-3 justify-center">
      <a href="javascript:history.back()" class="bg-red-600 text-white px-6 py-3 rounded-xl font-medium hover:bg-red-700 transition-colors flex items-center gap-2">
        <i data-lucide="arrow-left" class="w-4 h-4"></i> Go back
      </a>
      <a href="<?= APP_URL ?>/" class="border border-gray-200 text-gray-700 px-6 py-3 rounded-xl font-medium hover:bg-gray-50 transition-colors flex items-center gap-2">
        <i data-lucide="home" class="w-4 h-4"></i> Home
      </a>
    </div>
  </div>
</div>
<?php require_once $_SERVER['DOCUMENT_ROOT'] . '/ByteClass/includes/footer-minimal.php'; ?>
