<?php
require_once $_SERVER['DOCUMENT_ROOT'] . '/ByteClass/config/constants.php';
require_once $_SERVER['DOCUMENT_ROOT'] . '/ByteClass/config/session.php';
require_once $_SERVER['DOCUMENT_ROOT'] . '/ByteClass/config/database.php';
require_once $_SERVER['DOCUMENT_ROOT'] . '/ByteClass/helpers/response.php';
require_once $_SERVER['DOCUMENT_ROOT'] . '/ByteClass/helpers/mailer.php';

if (isset($_SESSION['user_id'])) {
    header('Location: ' . APP_URL . '/student/dashboard.php'); exit;
}

$error = $success = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $full_name = sanitize($_POST['full_name'] ?? '');
    $email     = strtolower(trim($_POST['email'] ?? ''));
    $phone     = sanitize($_POST['phone'] ?? '');
    $address   = sanitize($_POST['address'] ?? '');
    $password  = $_POST['password'] ?? '';
    $confirm   = $_POST['confirm_password'] ?? '';

    if (!$full_name || !$email || !$phone || !$address || !$password) {
        $error = 'All fields are required.';
    } elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $error = 'Invalid email address.';
    } elseif (strlen($password) < 8) {
        $error = 'Password must be at least 8 characters.';
    } elseif ($password !== $confirm) {
        $error = 'Passwords do not match.';
    } else {
        $db = Database::getInstance()->getConnection();
        $exists = $db->prepare("SELECT id FROM users WHERE email = ? LIMIT 1");
        $exists->execute([$email]);
        if ($exists->fetch()) {
            $error = 'An account with this email already exists.';
        } else {
            $hash  = password_hash($password, PASSWORD_BCRYPT, ['cost' => 12]);
            $token = generate_token(64);

            $stmt = $db->prepare("
                INSERT INTO users (full_name, email, phone, address, password_hash, role, status, email_verified, email_verify_token)
                VALUES (?, ?, ?, ?, ?, 'student', 'pending', 0, ?)
            ");
            $stmt->execute([$full_name, $email, $phone, $address, $hash, $token]);
            $user_id = (int)$db->lastInsertId();

            // Create student profile
            $db->prepare("INSERT INTO student_profiles (user_id) VALUES (?)")->execute([$user_id]);

            // Send verification email
            $sent = send_email_verification($email, $full_name, $token);

            $db->prepare("INSERT INTO activity_logs (user_id, action, description, ip_address) VALUES (?,?,?,?)")
               ->execute([$user_id, 'register', 'Student registered', $_SERVER['REMOTE_ADDR'] ?? '']);

            if ($sent) {
                $success = 'Account created! Please check your email to verify your account before logging in.';
            } else {
                $success = 'Account created! Verification email could not be sent — please contact support to activate your account.';
            }
        }
    }
}

$page_title = 'Create Account';
require_once $_SERVER['DOCUMENT_ROOT'] . '/ByteClass/includes/head.php';
?>
<div class="min-h-screen bg-gradient-to-br from-indigo-50 via-white to-cyan-50 flex items-center justify-center px-4 py-12">
  <div class="w-full max-w-lg">
    <div class="text-center mb-8">
      <a href="<?= APP_URL ?>/" class="inline-block mb-6">
        <span class="text-3xl font-bold">
          <span class="text-cyan-500">Byte</span><span class="text-gray-900">Class</span>
        </span>
      </a>
      <h1 class="text-2xl font-bold text-gray-900">Create your account</h1>
      <p class="text-gray-500 mt-1 text-sm">Join ByteClass and start learning today</p>
    </div>

    <div class="bg-white rounded-2xl shadow-sm border border-gray-100 p-8">

      <?php if ($error): ?>
      <div class="mb-5 p-4 bg-red-50 border border-red-200 rounded-xl flex items-center gap-3">
        <i data-lucide="alert-circle" class="w-5 h-5 text-red-500 flex-shrink-0"></i>
        <p class="text-sm text-red-700"><?= htmlspecialchars($error) ?></p>
      </div>
      <?php endif; ?>

      <?php if ($success): ?>
      <div class="mb-5 p-4 bg-green-50 border border-green-200 rounded-xl">
        <div class="flex items-start gap-3">
          <i data-lucide="check-circle" class="w-5 h-5 text-green-500 flex-shrink-0 mt-0.5"></i>
          <div>
            <p class="text-sm font-medium text-green-800">Account created successfully!</p>
            <p class="text-sm text-green-700 mt-1"><?= htmlspecialchars($success) ?></p>
            <a href="<?= APP_URL ?>/pages/auth/login.php"
               class="inline-block mt-3 text-sm font-medium text-green-700 underline">
              Go to login →
            </a>
          </div>
        </div>
      </div>
      <?php else: ?>

      <form method="POST" action="" class="space-y-4">
        <div>
          <label class="block text-sm font-medium text-gray-700 mb-1.5">Full name *</label>
          <input type="text" name="full_name" required
            value="<?= htmlspecialchars($_POST['full_name'] ?? '') ?>"
            placeholder="Your full name"
            class="w-full px-4 py-3 rounded-xl border border-gray-200 focus:outline-none focus:ring-2 focus:ring-indigo-500 text-sm" />
        </div>

        <div>
          <label class="block text-sm font-medium text-gray-700 mb-1.5">Email address *</label>
          <input type="email" name="email" required
            value="<?= htmlspecialchars($_POST['email'] ?? '') ?>"
            placeholder="you@example.com"
            class="w-full px-4 py-3 rounded-xl border border-gray-200 focus:outline-none focus:ring-2 focus:ring-indigo-500 text-sm" />
        </div>

        <div class="grid grid-cols-2 gap-4">
          <div>
            <label class="block text-sm font-medium text-gray-700 mb-1.5">Phone *</label>
            <input type="tel" name="phone" required
              value="<?= htmlspecialchars($_POST['phone'] ?? '') ?>"
              placeholder="+254700000000"
              class="w-full px-4 py-3 rounded-xl border border-gray-200 focus:outline-none focus:ring-2 focus:ring-indigo-500 text-sm" />
          </div>
          <div>
            <label class="block text-sm font-medium text-gray-700 mb-1.5">Address *</label>
            <input type="text" name="address" required
              value="<?= htmlspecialchars($_POST['address'] ?? '') ?>"
              placeholder="Nairobi, Kenya"
              class="w-full px-4 py-3 rounded-xl border border-gray-200 focus:outline-none focus:ring-2 focus:ring-indigo-500 text-sm" />
          </div>
        </div>

        <div>
          <label class="block text-sm font-medium text-gray-700 mb-1.5">Password *</label>
          <div class="relative">
            <input type="password" name="password" required id="pwd" minlength="8"
              placeholder="Min 8 characters"
              class="w-full px-4 py-3 rounded-xl border border-gray-200 focus:outline-none focus:ring-2 focus:ring-indigo-500 text-sm pr-12" />
            <button type="button" onclick="togglePwd('pwd','icon-pwd')"
              class="absolute right-3 top-1/2 -translate-y-1/2 text-gray-400 hover:text-gray-600">
              <i data-lucide="eye" class="w-4 h-4" id="icon-pwd"></i>
            </button>
          </div>
        </div>

        <div>
          <label class="block text-sm font-medium text-gray-700 mb-1.5">Confirm password *</label>
          <input type="password" name="confirm_password" required id="confirm"
            placeholder="Repeat your password"
            class="w-full px-4 py-3 rounded-xl border border-gray-200 focus:outline-none focus:ring-2 focus:ring-indigo-500 text-sm"
            oninput="checkPwdMatch()" />
          <p id="pwd-match" class="text-xs mt-1 hidden"></p>
        </div>

        <div class="bg-indigo-50 border border-indigo-100 rounded-xl p-3">
          <p class="text-xs text-indigo-700">
            By creating an account you agree to our
            <a href="#" class="underline font-medium">Terms & Conditions</a> and
            <a href="#" class="underline font-medium">Privacy Policy</a>.
          </p>
        </div>

        <button type="submit"
          class="w-full bg-indigo-600 hover:bg-indigo-700 text-white py-3 rounded-xl font-semibold text-sm transition-all flex items-center justify-center gap-2">
          <i data-lucide="user-plus" class="w-4 h-4"></i>
          Create account
        </button>
      </form>

      <p class="text-center text-sm text-gray-500 mt-6">
        Already have an account?
        <a href="<?= APP_URL ?>/pages/auth/login.php" class="text-indigo-600 font-medium hover:underline">
          Sign in
        </a>
      </p>
      <?php endif; ?>
    </div>
  </div>
</div>

<script>
function togglePwd(id, iconId) {
  const el   = document.getElementById(id);
  const icon = document.getElementById(iconId);
  if (el.type === 'password') {
    el.type = 'text';
    icon.setAttribute('data-lucide','eye-off');
  } else {
    el.type = 'password';
    icon.setAttribute('data-lucide','eye');
  }
  lucide.createIcons();
}
function checkPwdMatch() {
  const p = document.getElementById('pwd').value;
  const c = document.getElementById('confirm').value;
  const m = document.getElementById('pwd-match');
  if (!c) { m.classList.add('hidden'); return; }
  m.classList.remove('hidden');
  if (p === c) {
    m.textContent = '✓ Passwords match';
    m.className = 'text-xs mt-1 text-green-600';
  } else {
    m.textContent = '✗ Passwords do not match';
    m.className = 'text-xs mt-1 text-red-500';
  }
}
</script>

<?php require_once $_SERVER['DOCUMENT_ROOT'] . '/ByteClass/includes/footer-minimal.php'; ?>
