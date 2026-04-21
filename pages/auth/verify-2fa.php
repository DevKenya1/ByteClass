<?php
require_once $_SERVER['DOCUMENT_ROOT'] . '/ByteClass/config/constants.php';
require_once $_SERVER['DOCUMENT_ROOT'] . '/ByteClass/config/session.php';
require_once $_SERVER['DOCUMENT_ROOT'] . '/ByteClass/config/database.php';
require_once $_SERVER['DOCUMENT_ROOT'] . '/ByteClass/helpers/response.php';
require_once $_SERVER['DOCUMENT_ROOT'] . '/ByteClass/helpers/otp.php';
require_once $_SERVER['DOCUMENT_ROOT'] . '/ByteClass/helpers/mailer.php';

$user_id = $_SESSION['2fa_pending_user_id'] ?? null;
if (!$user_id) {
    header('Location: ' . APP_URL . '/pages/auth/login.php'); exit;
}

$db     = Database::getInstance()->getConnection();
$error  = '';
$resent = false;

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $action = $_POST['action'] ?? '';

    if ($action === 'verify') {
        $code = trim(implode('', array_map(fn($k) => $_POST["digit_$k"] ?? '', range(1,6))));
        if (strlen($code) !== 6) {
            $error = 'Please enter all 6 digits.';
        } elseif (verify_otp($user_id, $code, '2fa')) {
            // OTP valid — complete login
            $user_stmt = $db->prepare("SELECT * FROM users WHERE id=? LIMIT 1");
            $user_stmt->execute([$user_id]); $user = $user_stmt->fetch();

            unset($_SESSION['2fa_pending_user_id']);
            session_regenerate_id(true);

            $_SESSION['user_id']   = (int)$user['id'];
            $_SESSION['full_name'] = $user['full_name'];
            $_SESSION['email']     = $user['email'];
            $_SESSION['role']      = $user['role'];
            $_SESSION['photo']     = $user['profile_photo'];

            $db->prepare("UPDATE users SET last_login=NOW() WHERE id=?")->execute([$user['id']]);

            if ($user['role'] === 'admin')        { header('Location: '.APP_URL.'/admin/dashboard.php');    exit; }
            elseif ($user['role'] === 'lecturer') { header('Location: '.APP_URL.'/lecturer/dashboard.php'); exit; }
            else                                  { header('Location: '.APP_URL.'/student/dashboard.php');  exit; }
        } else {
            $error = 'Invalid or expired code. Please try again.';
        }
    }

    if ($action === 'resend') {
        $user_stmt = $db->prepare("SELECT * FROM users WHERE id=? LIMIT 1");
        $user_stmt->execute([$user_id]); $user = $user_stmt->fetch();
        if ($user) {
            $otp = generate_and_store_otp($user_id, '2fa');
            send_otp_email($user['email'], $user['full_name'], $otp);
            $resent = true;
        }
    }
}

$page_title = 'Two-Factor Verification';
require_once $_SERVER['DOCUMENT_ROOT'] . '/ByteClass/includes/head.php';
?>
<div class="min-h-screen bg-gradient-to-br from-indigo-50 via-white to-cyan-50 flex items-center justify-center px-4">
  <div class="w-full max-w-md">
    <div class="text-center mb-8">
      <a href="<?= APP_URL ?>/" class="inline-block mb-6">
        <span class="text-3xl font-bold"><span class="text-cyan-500">Byte</span><span class="text-gray-900">Class</span></span>
      </a>
      <div class="w-16 h-16 bg-indigo-100 rounded-full flex items-center justify-center mx-auto mb-4">
        <i data-lucide="shield-check" class="w-8 h-8 text-indigo-600"></i>
      </div>
      <h1 class="text-2xl font-bold text-gray-900">Two-Factor Verification</h1>
      <p class="text-gray-500 mt-2 text-sm">Enter the 6-digit code sent to your email address</p>
    </div>

    <div class="bg-white rounded-2xl shadow-sm border border-gray-100 p-8">
      <?php if ($error): ?>
      <div class="mb-5 p-4 bg-red-50 border border-red-200 rounded-xl flex items-center gap-3">
        <i data-lucide="alert-circle" class="w-5 h-5 text-red-500 flex-shrink-0"></i>
        <p class="text-sm text-red-700"><?= htmlspecialchars($error) ?></p>
      </div>
      <?php endif; ?>

      <?php if ($resent): ?>
      <div class="mb-5 p-4 bg-green-50 border border-green-200 rounded-xl flex items-center gap-3">
        <i data-lucide="mail" class="w-5 h-5 text-green-500 flex-shrink-0"></i>
        <p class="text-sm text-green-700">New code sent to your email!</p>
      </div>
      <?php endif; ?>

      <form method="POST" action="" id="otp-form">
        <input type="hidden" name="action" value="verify">
        <!-- 6-digit OTP input -->
        <div class="flex justify-center gap-3 mb-8">
          <?php for($i=1; $i<=6; $i++): ?>
          <input type="text" name="digit_<?= $i ?>" id="d<?= $i ?>"
            maxlength="1" pattern="[0-9]" inputmode="numeric"
            class="w-12 h-14 text-center text-2xl font-bold border-2 border-gray-200 rounded-xl focus:outline-none focus:border-indigo-500 focus:ring-2 focus:ring-indigo-100 transition-all"
            onkeyup="moveNext(this, <?= $i ?>, event)"
            onpaste="handlePaste(event)" />
          <?php endfor; ?>
        </div>

        <button type="submit" id="verify-btn"
          class="w-full bg-indigo-600 hover:bg-indigo-700 text-white py-3 rounded-xl font-semibold text-sm transition-all flex items-center justify-center gap-2 mb-4">
          <i data-lucide="shield-check" class="w-4 h-4"></i> Verify & Login
        </button>
      </form>

      <div class="text-center space-y-3">
        <p class="text-sm text-gray-500">Didn't receive the code?</p>
        <form method="POST" class="inline">
          <input type="hidden" name="action" value="resend">
          <button type="submit" id="resend-btn"
            class="text-sm text-indigo-600 font-medium hover:underline flex items-center gap-1 mx-auto">
            <i data-lucide="refresh-cw" class="w-3.5 h-3.5"></i>
            Resend code
          </button>
        </form>
        <p class="text-xs text-gray-400">Code expires in 10 minutes</p>
        <a href="<?= APP_URL ?>/pages/auth/login.php"
           class="text-xs text-gray-400 hover:text-gray-600 flex items-center justify-center gap-1 mt-2">
          <i data-lucide="arrow-left" class="w-3.5 h-3.5"></i> Back to login
        </a>
      </div>
    </div>
  </div>
</div>

<script>
function moveNext(el, idx, e) {
  if (e.key === 'Backspace' && !el.value && idx > 1) {
    document.getElementById('d'+(idx-1)).focus();
    return;
  }
  if (el.value && idx < 6) {
    document.getElementById('d'+(idx+1)).focus();
  }
  // Auto-submit when all 6 filled
  const vals = Array.from({length:6}, (_,i) => document.getElementById('d'+(i+1)).value);
  if (vals.every(v => v !== '')) {
    setTimeout(() => document.getElementById('otp-form').submit(), 100);
  }
}

function handlePaste(e) {
  e.preventDefault();
  const text = (e.clipboardData || window.clipboardData).getData('text').replace(/\D/g,'').substring(0,6);
  text.split('').forEach((ch, i) => {
    const el = document.getElementById('d'+(i+1));
    if (el) el.value = ch;
  });
  if (text.length === 6) {
    setTimeout(() => document.getElementById('otp-form').submit(), 100);
  }
}

// Focus first digit on load
document.getElementById('d1').focus();
</script>

<?php require_once $_SERVER['DOCUMENT_ROOT'] . '/ByteClass/includes/footer-minimal.php'; ?>
