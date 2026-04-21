<?php
require_once $_SERVER['DOCUMENT_ROOT'] . '/ByteClass/config/constants.php';
require_once $_SERVER['DOCUMENT_ROOT'] . '/ByteClass/config/session.php';
http_response_code(404);
$page_title = '404 — Page Not Found';
require_once $_SERVER['DOCUMENT_ROOT'] . '/ByteClass/includes/head.php';
?>
<div class="min-h-screen bg-gradient-to-br from-indigo-50 via-white to-cyan-50 flex items-center justify-center px-4">
  <div class="text-center max-w-md">
    <div class="text-8xl font-black text-indigo-600 mb-4 leading-none">404</div>
    <h1 class="text-2xl font-bold text-gray-900 mb-3">Page not found</h1>
    <p class="text-gray-500 mb-8">The page you are looking for doesn't exist or has been moved.</p>
    <div class="flex gap-3 justify-center flex-wrap">
      <?php
      require_once $_SERVER['DOCUMENT_ROOT'] . '/ByteClass/helpers/response.php';
      if (is_logged_in()):
        $dash = match($_SESSION['role'] ?? '') {
          'admin'    => APP_URL . '/admin/dashboard.php',
          'lecturer' => APP_URL . '/lecturer/dashboard.php',
          default    => APP_URL . '/student/dashboard.php',
        };
      ?>
      <a href="<?= $dash ?>" class="bg-indigo-600 text-white px-6 py-3 rounded-xl font-medium hover:bg-indigo-700 transition-colors flex items-center gap-2">
        <i data-lucide="layout-dashboard" class="w-4 h-4"></i> Go to dashboard
      </a>
      <?php else: ?>
      <a href="<?= APP_URL ?>/pages/auth/login.php" class="bg-indigo-600 text-white px-6 py-3 rounded-xl font-medium hover:bg-indigo-700 transition-colors flex items-center gap-2">
        <i data-lucide="log-in" class="w-4 h-4"></i> Login
      </a>
      <?php endif; ?>
      <a href="<?= APP_URL ?>/" class="border border-gray-200 text-gray-700 px-6 py-3 rounded-xl font-medium hover:bg-gray-50 transition-colors flex items-center gap-2">
        <i data-lucide="home" class="w-4 h-4"></i> Home
      </a>
    </div>
  </div>
</div>
<?php require_once $_SERVER['DOCUMENT_ROOT'] . '/ByteClass/includes/footer-minimal.php'; ?>
