<?php
require_once $_SERVER['DOCUMENT_ROOT'] . '/ByteClass/config/constants.php';
require_once $_SERVER['DOCUMENT_ROOT'] . '/ByteClass/config/session.php';
require_once $_SERVER['DOCUMENT_ROOT'] . '/ByteClass/config/database.php';
require_once $_SERVER['DOCUMENT_ROOT'] . '/ByteClass/helpers/response.php';
require_once $_SERVER['DOCUMENT_ROOT'] . '/ByteClass/helpers/jwt.php';
require_once $_SERVER['DOCUMENT_ROOT'] . '/ByteClass/middleware/ratelimit.php';

// Redirect if already logged in
if (isset($_SESSION['user_id']) && isset($_SESSION['role'])) {
    $role = $_SESSION['role'];
    if ($role === 'admin')          { header('Location: ' . APP_URL . '/admin/dashboard.php');    exit; }
    elseif ($role === 'lecturer')   { header('Location: ' . APP_URL . '/lecturer/dashboard.php'); exit; }
    else                            { header('Location: ' . APP_URL . '/student/dashboard.php');  exit; }
}

$error   = '';
$success = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $email    = strtolower(trim($_POST['email'] ?? ''));
    $password = $_POST['password'] ?? '';
    $device   = $_POST['device_type'] ?? 'laptop';

    if (!$email || !$password) {
        $error = 'Email and password are required.';
    } else {
        check_login_rate($email);
        $db   = Database::getInstance()->getConnection();
        $stmt = $db->prepare("SELECT * FROM users WHERE email = ? LIMIT 1");
        $stmt->execute([$email]);
        $user = $stmt->fetch();

        if (!$user) {
            $error = 'Invalid email or password.';
        } elseif ($user['status'] === 'locked') {
            $error = 'Account locked due to too many failed attempts. Contact admin.';
        } elseif ($user['status'] === 'inactive') {
            $error = 'Account deactivated. Please contact admin.';
        } elseif ($user['status'] === 'pending') {
            $error = 'Account pending. Please verify your email first.';
        } elseif (!$user['email_verified']) {
            $error = 'Please verify your email address before logging in.';
        } elseif (!password_verify($password, $user['password_hash'])) {
            increment_login_attempts($user['id']);
            $error = 'Invalid email or password.';
        } else {
            reset_login_attempts($user['id']);

            // Manage device sessions (not for admins)
            if ($user['role'] !== 'admin') {
                $sess_stmt = $db->prepare("SELECT * FROM user_sessions WHERE user_id = ? ORDER BY last_active ASC");
                $sess_stmt->execute([$user['id']]);
                $existing = $sess_stmt->fetchAll();
                if (count($existing) >= MAX_DEVICES) {
                    $db->prepare("DELETE FROM user_sessions WHERE id = ?")->execute([$existing[0]['id']]);
                }
            }

            // Create JWT token
            $token = jwt_encode_token([
                'user_id' => $user['id'],
                'role'    => $user['role'],
                'email'   => $user['email'],
            ]);

            // Store session in DB
            $db->prepare(
                "INSERT INTO user_sessions (user_id, token, device_type, ip_address, user_agent)
                 VALUES (?, ?, ?, ?, ?)"
            )->execute([
                $user['id'],
                $token,
                $device,
                $_SERVER['REMOTE_ADDR'] ?? '',
                $_SERVER['HTTP_USER_AGENT'] ?? '',
            ]);

            // Store in PHP session — use exact role from database
            session_regenerate_id(true);
            $_SESSION['user_id']   = (int)$user['id'];
            $_SESSION['full_name'] = $user['full_name'];
            $_SESSION['email']     = $user['email'];
            $_SESSION['role']      = $user['role']; // ALWAYS from DB, never from form
            $_SESSION['photo']     = $user['profile_photo'];
            $_SESSION['token']     = $token;

            // Update last login
            // Award daily login points for students
if ($user['role'] === 'student') {
    require_once $_SERVER['DOCUMENT_ROOT'] . '/ByteClass/helpers/points.php';
    award_daily_login($user['id']);
}

            // Log the login
            $db->prepare(
                "INSERT INTO activity_logs (user_id, action, description, ip_address)
                 VALUES (?, 'login', 'User logged in', ?)"
            )->execute([$user['id'], $_SERVER['REMOTE_ADDR'] ?? '']);

            // Redirect based on role from DB
            if ($user['role'] === 'admin') {
                header('Location: ' . APP_URL . '/admin/dashboard.php');
            } elseif ($user['role'] === 'lecturer') {
                header('Location: ' . APP_URL . '/lecturer/dashboard.php');
            } else {
                header('Location: ' . APP_URL . '/student/dashboard.php');
            }
            exit;
        }
    }
}

$page_title = 'Login';
require_once $_SERVER['DOCUMENT_ROOT'] . '/ByteClass/includes/head.php';
?>

<div class="min-h-screen bg-gradient-to-br from-indigo-50 via-white to-cyan-50 flex items-center justify-center px-4 py-12">
  <div class="w-full max-w-md">

    <div class="text-center mb-8">
      <a href="<?= APP_URL ?>/" class="inline-block mb-6">
        <span class="text-3xl font-bold">
          <span class="text-cyan-500">Byte</span><span class="text-gray-900">Class</span>
        </span>
      </a>
      <h1 class="text-2xl font-bold text-gray-900">Welcome back</h1>
      <p class="text-gray-500 mt-1 text-sm">Sign in to your account to continue</p>
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
        <i data-lucide="check-circle" class="w-5 h-5 text-green-500 flex-shrink-0"></i>
        <p class="text-sm text-green-700"><?= htmlspecialchars($success) ?></p>
      </div>
      <?php endif; ?>

      <form method="POST" action="" class="space-y-5">

        <div>
          <label class="block text-sm font-medium text-gray-700 mb-1.5" for="email">
            Email address
          </label>
          <input type="email" id="email" name="email" required autocomplete="email"
            value="<?= htmlspecialchars($_POST['email'] ?? '') ?>"
            placeholder="you@example.com"
            class="w-full px-4 py-3 rounded-xl border border-gray-200 focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:border-transparent text-sm transition-all" />
        </div>

        <div>
          <label class="block text-sm font-medium text-gray-700 mb-1.5" for="password">
            Password
          </label>
          <div class="relative">
            <input type="password" id="password" name="password" required autocomplete="current-password"
              placeholder="Enter your password"
              class="w-full px-4 py-3 rounded-xl border border-gray-200 focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:border-transparent text-sm transition-all pr-12" />
            <button type="button" onclick="togglePassword()"
              class="absolute right-3 top-1/2 -translate-y-1/2 text-gray-400 hover:text-gray-600">
              <i data-lucide="eye" class="w-5 h-5" id="pwd-icon"></i>
            </button>
          </div>
        </div>

        <div>
          <label class="block text-sm font-medium text-gray-700 mb-1.5" for="device_type">
            Logging in from
          </label>
          <select id="device_type" name="device_type"
            class="w-full px-4 py-3 rounded-xl border border-gray-200 focus:outline-none focus:ring-2 focus:ring-indigo-500 text-sm">
            <option value="laptop">Laptop / Desktop</option>
            <option value="phone">Phone</option>
            <option value="other">Other device</option>
          </select>
        </div>

        <div class="flex justify-end">
          <a href="<?= APP_URL ?>/pages/auth/forgot-password.php"
             class="text-sm text-indigo-600 hover:underline">
            Forgot password?
          </a>
        </div>

        <button type="submit"
          class="w-full bg-indigo-600 hover:bg-indigo-700 text-white py-3 rounded-xl font-semibold text-sm transition-all flex items-center justify-center gap-2">
          <i data-lucide="log-in" class="w-4 h-4"></i>
          Sign in
        </button>

      </form>

      <p class="text-center text-sm text-gray-500 mt-6">
        Don't have an account?
        <a href="<?= APP_URL ?>/pages/auth/register.php"
           class="text-indigo-600 font-medium hover:underline">
          Create one free
        </a>
      </p>
    </div>

  </div>
</div>

<script>
function togglePassword() {
  const pwd  = document.getElementById('password');
  const icon = document.getElementById('pwd-icon');
  if (pwd.type === 'password') {
    pwd.type = 'text';
    icon.setAttribute('data-lucide', 'eye-off');
  } else {
    pwd.type = 'password';
    icon.setAttribute('data-lucide', 'eye');
  }
  lucide.createIcons();
}
</script>

<?php require_once $_SERVER['DOCUMENT_ROOT'] . '/ByteClass/includes/footer-minimal.php'; ?>
