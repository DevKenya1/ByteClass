<?php
require_once $_SERVER['DOCUMENT_ROOT'] . '/ByteClass/config/constants.php';
require_once $_SERVER['DOCUMENT_ROOT'] . '/ByteClass/config/session.php';
require_once $_SERVER['DOCUMENT_ROOT'] . '/ByteClass/config/database.php';
require_once $_SERVER['DOCUMENT_ROOT'] . '/ByteClass/helpers/response.php';

$token   = sanitize($_GET['token'] ?? '');
$error   = $success = '';
$valid   = false;
$user_id = null;

if ($token) {
    $db   = Database::getInstance()->getConnection();
    $stmt = $db->prepare("SELECT pr.*, u.full_name FROM password_resets pr JOIN users u ON u.id=pr.user_id WHERE pr.token=? AND pr.used=0 AND pr.expires_at > NOW() LIMIT 1");
    $stmt->execute([$token]);
    $reset = $stmt->fetch();
    if ($reset) {
        $valid   = true;
        $user_id = $reset['user_id'];
    } else {
        $error = 'This reset link is invalid or has expired. Please request a new one.';
    }
}

if ($_SERVER['REQUEST_METHOD'] === 'POST' && $valid) {
    $password = $_POST['password'] ?? '';
    $confirm  = $_POST['confirm']  ?? '';
    if (strlen($password) < 8) {
        $error = 'Password must be at least 8 characters.';
    } elseif ($password !== $confirm) {
        $error = 'Passwords do not match.';
    } else {
        $hash = password_hash($password, PASSWORD_BCRYPT, ['cost' => 12]);
        $db->prepare("UPDATE users SET password_hash=?, updated_at=NOW() WHERE id=?")->execute([$hash, $user_id]);
        $db->prepare("UPDATE password_resets SET used=1 WHERE token=?")->execute([$token]);
        $db->prepare("DELETE FROM user_sessions WHERE user_id=?")->execute([$user_id]);
        $success = 'Password reset successfully! You can now log in with your new password.';
        $valid = false;
    }
}

$page_title = 'Reset Password';
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
      <h1 class="text-2xl font-bold text-gray-900">Set new password</h1>
      <p class="text-gray-500 mt-1 text-sm">Choose a strong password for your account</p>
    </div>

    <div class="bg-white rounded-2xl shadow-sm border border-gray-100 p-8">

      <?php if ($error): ?>
      <div class="mb-5 p-4 bg-red-50 border border-red-200 rounded-xl flex items-center gap-3">
        <i data-lucide="alert-circle" class="w-5 h-5 text-red-500 flex-shrink-0"></i>
        <p class="text-sm text-red-700"><?= htmlspecialchars($error) ?></p>
        <?php if (!$valid): ?>
        <a href="<?= APP_URL ?>/pages/auth/forgot-password.php" class="block mt-2 text-sm text-indigo-600 underline">Request new link</a>
        <?php endif; ?>
      </div>
      <?php endif; ?>

      <?php if ($success): ?>
      <div class="mb-5 p-4 bg-green-50 border border-green-200 rounded-xl flex items-center gap-3">
        <i data-lucide="check-circle" class="w-5 h-5 text-green-500 flex-shrink-0"></i>
        <p class="text-sm text-green-700"><?= htmlspecialchars($success) ?></p>
      </div>
      <a href="<?= APP_URL ?>/pages/auth/login.php"
         class="w-full bg-indigo-600 text-white py-3 rounded-xl font-semibold text-sm flex items-center justify-center gap-2 hover:bg-indigo-700 transition-colors">
        <i data-lucide="log-in" class="w-4 h-4"></i> Login now
      </a>
      <?php elseif ($valid): ?>
      <form method="POST" action="" class="space-y-5">
        <div>
          <label class="block text-sm font-medium text-gray-700 mb-1.5">New password</label>
          <div class="relative">
            <input type="password" name="password" required id="pwd" minlength="8"
              placeholder="Min 8 characters"
              class="w-full px-4 py-3 rounded-xl border border-gray-200 focus:outline-none focus:ring-2 focus:ring-indigo-500 text-sm pr-12" />
            <button type="button" onclick="togglePwd()"
              class="absolute right-3 top-1/2 -translate-y-1/2 text-gray-400 hover:text-gray-600">
              <i data-lucide="eye" class="w-4 h-4" id="eye-icon"></i>
            </button>
          </div>
        </div>
        <div>
          <label class="block text-sm font-medium text-gray-700 mb-1.5">Confirm new password</label>
          <input type="password" name="confirm" required id="confirm"
            placeholder="Repeat new password"
            class="w-full px-4 py-3 rounded-xl border border-gray-200 focus:outline-none focus:ring-2 focus:ring-indigo-500 text-sm"
            oninput="checkMatch()" />
          <p id="match-msg" class="text-xs mt-1 hidden"></p>
        </div>
        <button type="submit"
          class="w-full bg-indigo-600 hover:bg-indigo-700 text-white py-3 rounded-xl font-semibold text-sm transition-all flex items-center justify-center gap-2">
          <i data-lucide="lock" class="w-4 h-4"></i> Reset password
        </button>
      </form>
      <?php endif; ?>

    </div>
  </div>
</div>
<script>
function togglePwd() {
  const el = document.getElementById('pwd');
  const ic = document.getElementById('eye-icon');
  if (el.type === 'password') { el.type = 'text'; ic.setAttribute('data-lucide','eye-off'); }
  else { el.type = 'password'; ic.setAttribute('data-lucide','eye'); }
  lucide.createIcons();
}
function checkMatch() {
  const p = document.getElementById('pwd').value;
  const c = document.getElementById('confirm').value;
  const m = document.getElementById('match-msg');
  if (!c) { m.classList.add('hidden'); return; }
  m.classList.remove('hidden');
  if (p === c) { m.textContent = '✓ Passwords match'; m.className = 'text-xs mt-1 text-green-600'; }
  else { m.textContent = '✗ Passwords do not match'; m.className = 'text-xs mt-1 text-red-500'; }
}
</script>
<?php require_once $_SERVER['DOCUMENT_ROOT'] . '/ByteClass/includes/footer-minimal.php'; ?>
