<?php
require_once $_SERVER['DOCUMENT_ROOT'] . '/ByteClass/config/constants.php';
require_once $_SERVER['DOCUMENT_ROOT'] . '/ByteClass/config/session.php';
require_once $_SERVER['DOCUMENT_ROOT'] . '/ByteClass/config/database.php';
require_once $_SERVER['DOCUMENT_ROOT'] . '/ByteClass/helpers/response.php';
require_once $_SERVER['DOCUMENT_ROOT'] . '/ByteClass/helpers/mailer.php';

$error = $success = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $email = strtolower(trim($_POST['email'] ?? ''));
    if (!$email || !filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $error = 'Please enter a valid email address.';
    } else {
        $db   = Database::getInstance()->getConnection();
        $stmt = $db->prepare("SELECT id, full_name, status FROM users WHERE email = ? LIMIT 1");
        $stmt->execute([$email]);
        $user = $stmt->fetch();

        // Always show success to prevent email enumeration
        $success = 'If that email exists in our system, a reset link has been sent.';

        if ($user && $user['status'] === 'active') {
            $token   = generate_token(64);
            $expires = date('Y-m-d H:i:s', strtotime('+24 hours'));

            // Delete old tokens for this user
            $db->prepare("DELETE FROM password_resets WHERE user_id = ?")->execute([$user['id']]);

            $db->prepare("INSERT INTO password_resets (user_id, token, expires_at) VALUES (?,?,?)")
               ->execute([$user['id'], $token, $expires]);

            send_password_reset_email($email, $user['full_name'], $token);
        }
    }
}

$page_title = 'Forgot Password';
require_once $_SERVER['DOCUMENT_ROOT'] . '/ByteClass/includes/head.php';
?>
<div class="min-h-screen bg-gradient-to-br from-indigo-50 via-white to-cyan-50 flex items-center justify-center px-4">
  <div class="w-full max-w-md">
    <div class="text-center mb-8">
      <a href="<?= APP_URL ?>/" class="inline-block mb-6">
        <span class="text-3xl font-bold">
          <span class="text-cyan-500">Byte</span><span class="text-gray-900">Class</span>
        </span>
      </a>
      <h1 class="text-2xl font-bold text-gray-900">Forgot your password?</h1>
      <p class="text-gray-500 mt-1 text-sm">Enter your email and we'll send you a reset link</p>
    </div>

    <div class="bg-white rounded-2xl shadow-sm border border-gray-100 p-8">

      <?php if ($error): ?>
      <div class="mb-5 p-4 bg-red-50 border border-red-200 rounded-xl flex items-center gap-3">
        <i data-lucide="alert-circle" class="w-5 h-5 text-red-500 flex-shrink-0"></i>
        <p class="text-sm text-red-700"><?= htmlspecialchars($error) ?></p>
      </div>
      <?php endif; ?>

      <?php if ($success): ?>
      <div class="mb-5 p-4 bg-green-50 border border-green-200 rounded-xl flex items-center gap-3">
        <i data-lucide="mail" class="w-5 h-5 text-green-500 flex-shrink-0"></i>
        <p class="text-sm text-green-700"><?= htmlspecialchars($success) ?></p>
      </div>
      <?php endif; ?>

      <?php if (!$success): ?>
      <form method="POST" action="" class="space-y-5">
        <div>
          <label class="block text-sm font-medium text-gray-700 mb-1.5">Email address</label>
          <input type="email" name="email" required autocomplete="email"
            value="<?= htmlspecialchars($_POST['email'] ?? '') ?>"
            placeholder="you@example.com"
            class="w-full px-4 py-3 rounded-xl border border-gray-200 focus:outline-none focus:ring-2 focus:ring-indigo-500 text-sm" />
        </div>
        <button type="submit"
          class="w-full bg-indigo-600 hover:bg-indigo-700 text-white py-3 rounded-xl font-semibold text-sm transition-all flex items-center justify-center gap-2">
          <i data-lucide="send" class="w-4 h-4"></i>
          Send reset link
        </button>
      </form>
      <?php endif; ?>

      <p class="text-center text-sm text-gray-500 mt-6">
        <a href="<?= APP_URL ?>/pages/auth/login.php" class="text-indigo-600 hover:underline flex items-center justify-center gap-1">
          <i data-lucide="arrow-left" class="w-4 h-4"></i> Back to login
        </a>
      </p>
    </div>
  </div>
</div>
<?php require_once $_SERVER['DOCUMENT_ROOT'] . '/ByteClass/includes/footer-minimal.php'; ?>
