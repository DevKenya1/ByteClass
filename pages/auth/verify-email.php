<?php
require_once $_SERVER['DOCUMENT_ROOT'] . '/ByteClass/config/constants.php';
require_once $_SERVER['DOCUMENT_ROOT'] . '/ByteClass/config/session.php';
require_once $_SERVER['DOCUMENT_ROOT'] . '/ByteClass/config/database.php';
require_once $_SERVER['DOCUMENT_ROOT'] . '/ByteClass/helpers/response.php';

$token   = sanitize($_GET['token'] ?? '');
$status  = 'error';
$message = 'Invalid or expired verification link.';

if ($token) {
    $db   = Database::getInstance()->getConnection();
    $stmt = $db->prepare("SELECT * FROM users WHERE email_verify_token = ? AND email_verified = 0 LIMIT 1");
    $stmt->execute([$token]);
    $user = $stmt->fetch();

    if ($user) {
        $db->prepare("UPDATE users SET email_verified = 1, status = 'active', email_verify_token = NULL WHERE id = ?")
           ->execute([$user['id']]);
        $db->prepare("INSERT INTO activity_logs (user_id, action, description, ip_address) VALUES (?,?,?,?)")
           ->execute([$user['id'], 'email_verified', 'Email verified', $_SERVER['REMOTE_ADDR'] ?? '']);
        $status  = 'success';
        $message = 'Your email has been verified successfully! You can now log in.';
    } elseif ($db->prepare("SELECT id FROM users WHERE email_verify_token = ? AND email_verified = 1 LIMIT 1")->execute([$token])) {
        $status  = 'already';
        $message = 'This email has already been verified. Please log in.';
    }
}

$page_title = 'Email Verification';
require_once $_SERVER['DOCUMENT_ROOT'] . '/ByteClass/includes/head.php';
?>
<div class="min-h-screen bg-gradient-to-br from-indigo-50 via-white to-cyan-50 flex items-center justify-center px-4">
  <div class="w-full max-w-md text-center">
    <a href="<?= APP_URL ?>/" class="inline-block mb-8">
      <span class="text-3xl font-bold">
        <span class="text-cyan-500">Byte</span><span class="text-gray-900">Class</span>
      </span>
    </a>

    <div class="bg-white rounded-2xl shadow-sm border border-gray-100 p-8">
      <?php if ($status === 'success'): ?>
      <div class="w-16 h-16 bg-green-100 rounded-full flex items-center justify-center mx-auto mb-4">
        <i data-lucide="check-circle" class="w-8 h-8 text-green-600"></i>
      </div>
      <h1 class="text-xl font-bold text-gray-900 mb-2">Email verified!</h1>
      <p class="text-gray-500 text-sm mb-6"><?= htmlspecialchars($message) ?></p>
      <a href="<?= APP_URL ?>/pages/auth/login.php"
         class="inline-block bg-indigo-600 text-white px-8 py-3 rounded-xl font-semibold text-sm hover:bg-indigo-700 transition-colors">
        Login now
      </a>

      <?php elseif ($status === 'already'): ?>
      <div class="w-16 h-16 bg-blue-100 rounded-full flex items-center justify-center mx-auto mb-4">
        <i data-lucide="info" class="w-8 h-8 text-blue-600"></i>
      </div>
      <h1 class="text-xl font-bold text-gray-900 mb-2">Already verified</h1>
      <p class="text-gray-500 text-sm mb-6"><?= htmlspecialchars($message) ?></p>
      <a href="<?= APP_URL ?>/pages/auth/login.php"
         class="inline-block bg-indigo-600 text-white px-8 py-3 rounded-xl font-semibold text-sm hover:bg-indigo-700 transition-colors">
        Login
      </a>

      <?php else: ?>
      <div class="w-16 h-16 bg-red-100 rounded-full flex items-center justify-center mx-auto mb-4">
        <i data-lucide="x-circle" class="w-8 h-8 text-red-500"></i>
      </div>
      <h1 class="text-xl font-bold text-gray-900 mb-2">Verification failed</h1>
      <p class="text-gray-500 text-sm mb-6"><?= htmlspecialchars($message) ?></p>
      <a href="<?= APP_URL ?>/pages/auth/register.php"
         class="inline-block bg-indigo-600 text-white px-8 py-3 rounded-xl font-semibold text-sm hover:bg-indigo-700 transition-colors">
        Register again
      </a>
      <?php endif; ?>
    </div>
  </div>
</div>
<?php require_once $_SERVER['DOCUMENT_ROOT'] . '/ByteClass/includes/footer-minimal.php'; ?>
