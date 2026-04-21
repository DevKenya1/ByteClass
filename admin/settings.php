<?php
$required_role = 'admin';
require_once $_SERVER['DOCUMENT_ROOT'] . '/ByteClass/includes/auth-check.php';
require_once $_SERVER['DOCUMENT_ROOT'] . '/ByteClass/config/constants.php';
require_once $_SERVER['DOCUMENT_ROOT'] . '/ByteClass/config/database.php';
require_once $_SERVER['DOCUMENT_ROOT'] . '/ByteClass/helpers/response.php';

$db = Database::getInstance()->getConnection();
$success_msg = '';
$error_msg   = '';

function get_setting(PDO $db, string $key): string {
    $s = $db->prepare("SELECT setting_value FROM system_settings WHERE setting_key = ?");
    $s->execute([$key]);
    return (string)($s->fetchColumn() ?? '');
}
function save_setting(PDO $db, string $key, string $value, int $admin_id): void {
    $db->prepare("UPDATE system_settings SET setting_value=?, updated_by=?, updated_at=NOW() WHERE setting_key=?")
       ->execute([$value, $admin_id, $key]);
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $section = $_POST['section'] ?? '';

    if ($section === 'profile') {
        require_once $_SERVER['DOCUMENT_ROOT'] . '/ByteClass/helpers/upload.php';
        $full_name = sanitize($_POST['full_name'] ?? '');
        $phone     = sanitize($_POST['phone']     ?? '');
        $address   = sanitize($_POST['address']   ?? '');
        $new_email = strtolower(trim($_POST['email'] ?? ''));

        if (!$full_name) {
            $error_msg = 'Full name is required.';
        } else {
            // Handle photo upload
            $photo_url = null;
            if (isset($_FILES['profile_photo']) && $_FILES['profile_photo']['error'] === 0) {
                $photo_url = upload_profile_photo($_FILES['profile_photo'], 'admin_' . $_SESSION['user_id']);
                if (!$photo_url) {
                    $error_msg = 'Invalid image. Use JPG, PNG or WebP under 5MB.';
                }
            }

            if (!$error_msg) {
                // Check email uniqueness
                if ($new_email && $new_email !== $_SESSION['email']) {
                    $exists = $db->prepare("SELECT id FROM users WHERE email=? AND id!=? LIMIT 1");
                    $exists->execute([$new_email, $_SESSION['user_id']]);
                    if ($exists->fetch()) {
                        $error_msg = 'That email address is already in use.';
                    }
                }
            }

            if (!$error_msg) {
                $update_email = ($new_email && $new_email !== $_SESSION['email']) ? $new_email : $_SESSION['email'];

                if ($photo_url) {
                    $db->prepare("UPDATE users SET full_name=?, phone=?, address=?, email=?, profile_photo=?, updated_at=NOW() WHERE id=?")
                       ->execute([$full_name, $phone, $address, $update_email, $photo_url, $_SESSION['user_id']]);
                    $_SESSION['photo'] = $photo_url;
                } else {
                    $db->prepare("UPDATE users SET full_name=?, phone=?, address=?, email=?, updated_at=NOW() WHERE id=?")
                       ->execute([$full_name, $phone, $address, $update_email, $_SESSION['user_id']]);
                }
                // Reload fresh from DB to sync session
                $fresh = $db->prepare("SELECT full_name, email, profile_photo FROM users WHERE id=? LIMIT 1");
                $fresh->execute([$_SESSION['user_id']]);
                $fresh_user = $fresh->fetch();
                if ($fresh_user) {
                    $_SESSION['full_name'] = $fresh_user['full_name'];
                    $_SESSION['email']     = $fresh_user['email'];
                    $_SESSION['photo']     = $fresh_user['profile_photo'];
                }
                $success_msg = 'Profile updated successfully.';
            }
        }
    }

    if ($section === 'password') {
        $current  = $_POST['current_password'] ?? '';
        $new_pass = $_POST['new_password']      ?? '';
        $confirm  = $_POST['confirm_password']  ?? '';

        $stmt = $db->prepare("SELECT password_hash FROM users WHERE id=? LIMIT 1");
        $stmt->execute([$_SESSION['user_id']]);
        $hash = $stmt->fetchColumn();

        if (!password_verify($current, $hash)) {
            $error_msg = 'Current password is incorrect.';
        } elseif (strlen($new_pass) < 8) {
            $error_msg = 'New password must be at least 8 characters.';
        } elseif ($new_pass !== $confirm) {
            $error_msg = 'Passwords do not match.';
        } else {
            $new_hash = password_hash($new_pass, PASSWORD_BCRYPT, ['cost' => 12]);
            $db->prepare("UPDATE users SET password_hash=?, updated_at=NOW() WHERE id=?")
               ->execute([$new_hash, $_SESSION['user_id']]);
            $db->prepare("DELETE FROM user_sessions WHERE user_id=? AND token!=?")
               ->execute([$_SESSION['user_id'], $_SESSION['token']]);
            $success_msg = 'Password changed. Other sessions have been logged out.';
        }
    }

    if ($section === 'general') {
        save_setting($db, 'platform_name',    sanitize($_POST['platform_name']    ?? ''), $_SESSION['user_id']);
        save_setting($db, 'platform_tagline', sanitize($_POST['platform_tagline'] ?? ''), $_SESSION['user_id']);
        save_setting($db, 'platform_email',   sanitize($_POST['platform_email']   ?? ''), $_SESSION['user_id']);
        $success_msg = 'General settings saved.';
    }

    if ($section === 'smtp') {
        save_setting($db, 'smtp_host',       sanitize($_POST['smtp_host']       ?? ''), $_SESSION['user_id']);
        save_setting($db, 'smtp_port',       sanitize($_POST['smtp_port']       ?? ''), $_SESSION['user_id']);
        save_setting($db, 'smtp_user',       sanitize($_POST['smtp_user']       ?? ''), $_SESSION['user_id']);
        if (!empty($_POST['smtp_pass'])) {
            save_setting($db, 'smtp_pass',   sanitize($_POST['smtp_pass']       ?? ''), $_SESSION['user_id']);
        }
        save_setting($db, 'smtp_from_name',  sanitize($_POST['smtp_from_name']  ?? ''), $_SESSION['user_id']);
        save_setting($db, 'smtp_from_email', sanitize($_POST['smtp_from_email'] ?? ''), $_SESSION['user_id']);
        $success_msg = 'SMTP settings saved.';
    }

    if ($section === 'security') {
        save_setting($db, 'auto_logout_minutes',       sanitize($_POST['auto_logout_minutes']       ?? '5'),  $_SESSION['user_id']);
        save_setting($db, 'max_login_attempts',        sanitize($_POST['max_login_attempts']        ?? '5'),  $_SESSION['user_id']);
        save_setting($db, 'max_devices',               sanitize($_POST['max_devices']               ?? '2'),  $_SESSION['user_id']);
        save_setting($db, 'two_fa_enabled',            isset($_POST['two_fa_enabled']) ? '1' : '0',            $_SESSION['user_id']);
        save_setting($db, 'password_reset_expiry_hrs', sanitize($_POST['password_reset_expiry_hrs'] ?? '24'), $_SESSION['user_id']);
        $success_msg = 'Security settings saved.';
    }

    if ($section === 'maintenance') {
        save_setting($db, 'maintenance_mode',    isset($_POST['maintenance_mode']) ? '1' : '0', $_SESSION['user_id']);
        save_setting($db, 'maintenance_message', sanitize($_POST['maintenance_message'] ?? ''),  $_SESSION['user_id']);
        $success_msg = 'Maintenance settings saved.';
    }

    if ($section === 'payments') {
        foreach (['mpesa','stripe','paypal','paystack'] as $gw) {
            save_setting($db, $gw . '_enabled', isset($_POST[$gw . '_enabled']) ? '1' : '0', $_SESSION['user_id']);
        }
        foreach (['mpesa_shortcode','mpesa_consumer_key','mpesa_consumer_secret','stripe_public_key','stripe_secret_key','paypal_client_id','paypal_client_secret'] as $k) {
            save_setting($db, $k, sanitize($_POST[$k] ?? ''), $_SESSION['user_id']);
        }
        $success_msg = 'Payment settings saved.';
    }

    if ($section === 'ai') {
        save_setting($db, 'gemini_api_key',      sanitize($_POST['gemini_api_key']      ?? ''), $_SESSION['user_id']);
        save_setting($db, 'grok_api_key',        sanitize($_POST['grok_api_key']        ?? ''), $_SESSION['user_id']);
        save_setting($db, 'learnpulse_provider', sanitize($_POST['learnpulse_provider'] ?? 'gemini'), $_SESSION['user_id']);
        save_setting($db, 'learnpulse_enabled',  isset($_POST['learnpulse_enabled']) ? '1' : '0', $_SESSION['user_id']);
        $success_msg = 'AI settings saved.';
    }

    if ($section === 'social') {
        $social_keys = ['social_website','social_whatsapp','social_telegram',
                        'social_twitter','social_facebook','social_instagram'];
        foreach ($social_keys as $k) {
            save_setting($db, $k, sanitize($_POST[$k] ?? ''), $_SESSION['user_id']);
        }
        $success_msg = 'Social links saved.';
    }
}

// Load current admin profile
$admin_profile = $db->prepare("SELECT * FROM users WHERE id=? LIMIT 1");
$admin_profile->execute([$_SESSION['user_id']]);
$admin = $admin_profile->fetch();

$tab = sanitize($_GET['tab'] ?? 'profile');
$page_title = 'System Settings';
require_once $_SERVER['DOCUMENT_ROOT'] . '/ByteClass/includes/head.php';
?>
<div class="flex min-h-screen bg-gray-50">
  <?php require_once $_SERVER['DOCUMENT_ROOT'] . '/ByteClass/includes/sidebar-admin.php'; ?>
  <div id="main-content" class="flex-1 flex flex-col" style="margin-left:256px;">
    <header class="bg-white border-b border-gray-200 px-6 py-3 flex items-center justify-between sticky top-0 z-30">
      <div class="flex items-center gap-3">
        <button onclick="toggleSidebar()" class="text-gray-500 hover:text-gray-700 p-1.5 rounded-lg hover:bg-gray-100"><i data-lucide="menu" class="w-5 h-5"></i></button>
        <h1 class="text-lg font-semibold text-gray-800">System Settings</h1>
      </div>
      <div class="w-8 h-8 bg-indigo-600 rounded-full flex items-center justify-center text-white text-xs font-bold"><?= strtoupper(substr($_SESSION['full_name'],0,1)) ?></div>
    </header>
    <main class="flex-1 p-6">
      <div class="bg-gradient-to-r from-indigo-600 to-cyan-500 rounded-2xl px-6 py-5 mb-6">
        <h2 class="text-white text-xl font-bold">System Settings</h2>
        <p class="text-indigo-100 text-sm mt-0.5">Configure platform behaviour, integrations and security</p>
      </div>

      <?php if ($success_msg): ?>
      <div class="mb-4 p-4 bg-green-50 border border-green-200 rounded-xl text-sm text-green-700 flex gap-3">
        <i data-lucide="check-circle" class="w-5 h-5 flex-shrink-0"></i><?= htmlspecialchars($success_msg) ?>
      </div>
      <?php endif; ?>
      <?php if ($error_msg): ?>
      <div class="mb-4 p-4 bg-red-50 border border-red-200 rounded-xl text-sm text-red-700 flex gap-3">
        <i data-lucide="alert-circle" class="w-5 h-5 flex-shrink-0"></i><?= htmlspecialchars($error_msg) ?>
      </div>
      <?php endif; ?>

      <!-- Tabs -->
      <div class="flex gap-1 mb-6 border-b border-gray-200 overflow-x-auto">
        <?php foreach ([
          ['tab'=>'profile',     'label'=>'My Profile',  'icon'=>'user'],
          ['tab'=>'general',     'label'=>'General',     'icon'=>'settings'],
          ['tab'=>'smtp',        'label'=>'SMTP Email',  'icon'=>'mail'],
          ['tab'=>'security',    'label'=>'Security',    'icon'=>'shield'],
          ['tab'=>'payments',    'label'=>'Payments',    'icon'=>'credit-card'],
          ['tab'=>'ai',          'label'=>'AI Tutor',    'icon'=>'zap'],
          ['tab'=>'maintenance', 'label'=>'Maintenance', 'icon'=>'wrench'],
          ['tab'=>'social',      'label'=>'Social Links', 'icon'=>'share-2'],
        ] as $t): ?>
        <a href="?tab=<?= $t['tab'] ?>"
           class="flex items-center gap-2 px-4 py-3 text-sm font-medium border-b-2 transition-colors whitespace-nowrap
           <?= $tab===$t['tab'] ? 'border-indigo-600 text-indigo-600' : 'border-transparent text-gray-500 hover:text-gray-700' ?>">
          <i data-lucide="<?= $t['icon'] ?>" class="w-4 h-4"></i><?= $t['label'] ?>
        </a>
        <?php endforeach; ?>
      </div>

      <div class="bg-white rounded-2xl border border-gray-100 p-6 max-w-2xl">

        <?php if ($tab === 'profile'): ?>
        <!-- Profile Info -->
        <h3 class="font-semibold text-gray-900 mb-5 flex items-center gap-2">
          <i data-lucide="user" class="w-5 h-5 text-indigo-600"></i> My Profile
        </h3>
        <div class="flex items-center gap-4 mb-6 p-4 bg-indigo-50 rounded-2xl">
          <div class="w-16 h-16 bg-indigo-600 rounded-2xl flex items-center justify-center text-white text-2xl font-bold">
            <?= strtoupper(substr($admin['full_name'], 0, 1)) ?>
          </div>
          <div>
            <p class="font-semibold text-gray-900"><?= htmlspecialchars($admin['full_name']) ?></p>
            <p class="text-sm text-gray-500"><?= htmlspecialchars($admin['email']) ?></p>
            <span class="text-xs bg-indigo-100 text-indigo-700 px-2 py-0.5 rounded-full">System Administrator</span>
          </div>
        </div>

        <form method="POST" enctype="multipart/form-data" class="space-y-4 mb-8">
          <input type="hidden" name="section" value="profile">

          <!-- Photo upload -->
          <div>
            <label class="block text-sm font-medium text-gray-700 mb-2">Profile photo</label>
            <div class="flex items-center gap-4">
              <div id="photo-preview" class="w-16 h-16 rounded-full overflow-hidden flex-shrink-0 border-2 border-gray-200 flex items-center justify-center bg-indigo-100">
                <?php if ($admin['profile_photo']): ?>
                <img src="<?= htmlspecialchars($admin['profile_photo']) ?>" alt="Photo" class="w-full h-full object-cover" id="preview-img" />
                <?php else: ?>
                <span class="text-2xl font-bold text-indigo-600" id="preview-initials"><?= strtoupper(substr($admin['full_name'],0,1)) ?></span>
                <?php endif; ?>
              </div>
              <div>
                <input type="file" name="profile_photo" id="photo-input" accept="image/*" class="hidden"
                  onchange="previewPhoto(this)" />
                <button type="button" onclick="document.getElementById('photo-input').click()"
                  class="px-4 py-2 border border-gray-200 rounded-xl text-sm text-gray-700 hover:bg-gray-50 flex items-center gap-2">
                  <i data-lucide="camera" class="w-4 h-4"></i> Change photo
                </button>
                <p class="text-xs text-gray-400 mt-1">JPG, PNG or WebP · Max 5MB · Auto-resized to 200×200</p>
              </div>
            </div>
          </div>

          <div>
            <label class="block text-sm font-medium text-gray-700 mb-1.5">Full name *</label>
            <input type="text" name="full_name" value="<?= htmlspecialchars($admin['full_name']) ?>" required
              class="w-full px-4 py-3 border border-gray-200 rounded-xl text-sm focus:outline-none focus:ring-2 focus:ring-indigo-500" />
          </div>

          <div>
            <label class="block text-sm font-medium text-gray-700 mb-1.5">Email address</label>
            <input type="email" name="email" value="<?= htmlspecialchars($admin['email']) ?>"
              class="w-full px-4 py-3 border border-gray-200 rounded-xl text-sm focus:outline-none focus:ring-2 focus:ring-indigo-500" />
            <p class="text-xs text-gray-400 mt-1">Changing your email will update your login email address.</p>
          </div>

          <div>
            <label class="block text-sm font-medium text-gray-700 mb-1.5">Phone</label>
            <input type="tel" name="phone" value="<?= htmlspecialchars($admin['phone'] ?? '') ?>"
              placeholder="+254700000000"
              class="w-full px-4 py-3 border border-gray-200 rounded-xl text-sm focus:outline-none focus:ring-2 focus:ring-indigo-500" />
          </div>

          <div>
            <label class="block text-sm font-medium text-gray-700 mb-1.5">Address</label>
            <input type="text" name="address" value="<?= htmlspecialchars($admin['address'] ?? '') ?>"
              placeholder="Nairobi, Kenya"
              class="w-full px-4 py-3 border border-gray-200 rounded-xl text-sm focus:outline-none focus:ring-2 focus:ring-indigo-500" />
          </div>

          <button type="submit" class="bg-indigo-600 text-white px-6 py-3 rounded-xl text-sm font-medium hover:bg-indigo-700 flex items-center gap-2">
            <i data-lucide="save" class="w-4 h-4"></i> Update Profile
          </button>
        </form>

        <hr class="border-gray-100 my-6">

        <!-- Change Password -->
        <h3 class="font-semibold text-gray-900 mb-5 flex items-center gap-2">
          <i data-lucide="lock" class="w-5 h-5 text-indigo-600"></i> Change Password
        </h3>
        <form method="POST" class="space-y-4">
          <input type="hidden" name="section" value="password">
          <div>
            <label class="block text-sm font-medium text-gray-700 mb-1.5">Current password *</label>
            <div class="relative">
              <input type="password" name="current_password" required id="pwd-current"
                placeholder="Enter current password"
                class="w-full px-4 py-3 border border-gray-200 rounded-xl text-sm focus:outline-none focus:ring-2 focus:ring-indigo-500 pr-12" />
              <button type="button" onclick="togglePwd('pwd-current','icon-current')"
                class="absolute right-3 top-1/2 -translate-y-1/2 text-gray-400 hover:text-gray-600">
                <i data-lucide="eye" class="w-4 h-4" id="icon-current"></i>
              </button>
            </div>
          </div>
          <div>
            <label class="block text-sm font-medium text-gray-700 mb-1.5">New password *</label>
            <div class="relative">
              <input type="password" name="new_password" required id="pwd-new" minlength="8"
                placeholder="Min 8 characters"
                class="w-full px-4 py-3 border border-gray-200 rounded-xl text-sm focus:outline-none focus:ring-2 focus:ring-indigo-500 pr-12" />
              <button type="button" onclick="togglePwd('pwd-new','icon-new')"
                class="absolute right-3 top-1/2 -translate-y-1/2 text-gray-400 hover:text-gray-600">
                <i data-lucide="eye" class="w-4 h-4" id="icon-new"></i>
              </button>
            </div>
          </div>
          <div>
            <label class="block text-sm font-medium text-gray-700 mb-1.5">Confirm new password *</label>
            <input type="password" name="confirm_password" required id="pwd-confirm"
              placeholder="Repeat new password"
              class="w-full px-4 py-3 border border-gray-200 rounded-xl text-sm focus:outline-none focus:ring-2 focus:ring-indigo-500"
              oninput="checkMatch()" />
            <p id="match-msg" class="text-xs mt-1 hidden"></p>
          </div>
          <div class="bg-amber-50 border border-amber-200 rounded-xl p-3 text-xs text-amber-700 flex gap-2">
            <i data-lucide="info" class="w-4 h-4 flex-shrink-0"></i>
            Changing your password will log out all your other active sessions.
          </div>
          <button type="submit" class="bg-rose-600 text-white px-6 py-3 rounded-xl text-sm font-medium hover:bg-rose-700 flex items-center gap-2">
            <i data-lucide="key" class="w-4 h-4"></i> Change Password
          </button>
        </form>

        <?php elseif ($tab === 'general'): ?>
        <h3 class="font-semibold text-gray-900 mb-5 flex items-center gap-2"><i data-lucide="settings" class="w-5 h-5 text-indigo-600"></i> General Settings</h3>
        <form method="POST" class="space-y-4">
          <input type="hidden" name="section" value="general">
          <?php foreach ([
            ['key'=>'platform_name',    'label'=>'Platform name'],
            ['key'=>'platform_tagline', 'label'=>'Tagline'],
            ['key'=>'platform_email',   'label'=>'Contact email'],
          ] as $f): ?>
          <div>
            <label class="block text-sm font-medium text-gray-700 mb-1.5"><?= $f['label'] ?></label>
            <input type="text" name="<?= $f['key'] ?>" value="<?= htmlspecialchars(get_setting($db, $f['key'])) ?>"
              class="w-full px-4 py-3 border border-gray-200 rounded-xl text-sm focus:outline-none focus:ring-2 focus:ring-indigo-500" />
          </div>
          <?php endforeach; ?>
          <button type="submit" class="bg-indigo-600 text-white px-6 py-3 rounded-xl text-sm font-medium hover:bg-indigo-700 flex items-center gap-2"><i data-lucide="save" class="w-4 h-4"></i> Save</button>
        </form>

        <?php elseif ($tab === 'smtp'): ?>
        <h3 class="font-semibold text-gray-900 mb-5 flex items-center gap-2"><i data-lucide="mail" class="w-5 h-5 text-indigo-600"></i> SMTP Email Settings</h3>
        <div class="bg-amber-50 border border-amber-200 rounded-xl p-3 text-xs text-amber-700 flex gap-2 mb-4">
          <i data-lucide="info" class="w-4 h-4 flex-shrink-0"></i>
          Use Gmail with App Password. Enable 2-step verification in Google → Security → App Passwords → Generate.
        </div>
        <form method="POST" class="space-y-4">
          <input type="hidden" name="section" value="smtp">
          <?php foreach ([
            ['key'=>'smtp_host','label'=>'SMTP host','type'=>'text','ph'=>'smtp.gmail.com'],
            ['key'=>'smtp_port','label'=>'SMTP port','type'=>'number','ph'=>'587'],
            ['key'=>'smtp_user','label'=>'Gmail address','type'=>'email','ph'=>'your@gmail.com'],
            ['key'=>'smtp_from_name','label'=>'From name','type'=>'text','ph'=>'ByteClass'],
            ['key'=>'smtp_from_email','label'=>'From email','type'=>'email','ph'=>'noreply@byteclass.io'],
          ] as $f): ?>
          <div>
            <label class="block text-sm font-medium text-gray-700 mb-1.5"><?= $f['label'] ?></label>
            <input type="<?= $f['type'] ?>" name="<?= $f['key'] ?>" value="<?= htmlspecialchars(get_setting($db,$f['key'])) ?>" placeholder="<?= $f['ph'] ?>"
              class="w-full px-4 py-3 border border-gray-200 rounded-xl text-sm focus:outline-none focus:ring-2 focus:ring-indigo-500" />
          </div>
          <?php endforeach; ?>
          <div>
            <label class="block text-sm font-medium text-gray-700 mb-1.5">Gmail App Password</label>
            <input type="password" name="smtp_pass" placeholder="Leave blank to keep existing"
              class="w-full px-4 py-3 border border-gray-200 rounded-xl text-sm focus:outline-none focus:ring-2 focus:ring-indigo-500" />
          </div>
          <button type="submit" class="bg-indigo-600 text-white px-6 py-3 rounded-xl text-sm font-medium hover:bg-indigo-700 flex items-center gap-2"><i data-lucide="save" class="w-4 h-4"></i> Save SMTP</button>
        </form>

        <?php elseif ($tab === 'security'): ?>
        <h3 class="font-semibold text-gray-900 mb-5 flex items-center gap-2"><i data-lucide="shield" class="w-5 h-5 text-indigo-600"></i> Security Settings</h3>
        <form method="POST" class="space-y-4">
          <input type="hidden" name="section" value="security">
          <?php foreach ([
            ['key'=>'auto_logout_minutes','label'=>'Auto logout (minutes)'],
            ['key'=>'max_login_attempts','label'=>'Max login attempts before lockout'],
            ['key'=>'max_devices','label'=>'Max simultaneous devices'],
            ['key'=>'password_reset_expiry_hrs','label'=>'Password reset link expiry (hours)'],
          ] as $f): ?>
          <div>
            <label class="block text-sm font-medium text-gray-700 mb-1.5"><?= $f['label'] ?></label>
            <input type="number" name="<?= $f['key'] ?>" value="<?= htmlspecialchars(get_setting($db,$f['key'])) ?>" min="1"
              class="w-full px-4 py-3 border border-gray-200 rounded-xl text-sm focus:outline-none focus:ring-2 focus:ring-indigo-500" />
          </div>
          <?php endforeach; ?>
          <label class="flex items-center gap-3 cursor-pointer p-3 border border-gray-200 rounded-xl hover:bg-gray-50">
            <input type="checkbox" name="two_fa_enabled" value="1" <?= get_setting($db,'two_fa_enabled')==='1'?'checked':'' ?> class="w-4 h-4 text-indigo-600 rounded">
            <span class="text-sm font-medium text-gray-700">Enable Two-Factor Authentication (2FA)</span>
          </label>
          <button type="submit" class="bg-indigo-600 text-white px-6 py-3 rounded-xl text-sm font-medium hover:bg-indigo-700 flex items-center gap-2"><i data-lucide="save" class="w-4 h-4"></i> Save</button>
        </form>

        <?php elseif ($tab === 'payments'): ?>
        <h3 class="font-semibold text-gray-900 mb-5 flex items-center gap-2"><i data-lucide="credit-card" class="w-5 h-5 text-indigo-600"></i> Payment Gateway Settings</h3>
        <form method="POST" class="space-y-4">
          <input type="hidden" name="section" value="payments">
          <div class="grid grid-cols-3 gap-3 mb-2">
            <?php foreach (['mpesa','stripe','paypal','paystack'] as $gw): ?>
            <label class="flex items-center gap-2 p-3 border border-gray-200 rounded-xl cursor-pointer hover:bg-gray-50">
              <input type="checkbox" name="<?= $gw ?>_enabled" value="1" <?= get_setting($db,$gw.'_enabled')==='1'?'checked':'' ?> class="w-4 h-4 text-indigo-600 rounded">
              <span class="text-sm font-medium"><?= strtoupper($gw) ?></span>
            </label>
            <?php endforeach; ?>
          </div>
          <p class="text-sm font-semibold text-gray-700 mt-4">M-Pesa (Daraja API)</p>
          <?php foreach (['mpesa_shortcode'=>'Shortcode','mpesa_consumer_key'=>'Consumer key','mpesa_consumer_secret'=>'Consumer secret'] as $k=>$l): ?>
          <div>
            <label class="block text-sm font-medium text-gray-700 mb-1.5"><?= $l ?></label>
            <input type="text" name="<?= $k ?>" value="<?= htmlspecialchars(get_setting($db,$k)) ?>" class="w-full px-4 py-3 border border-gray-200 rounded-xl text-sm focus:outline-none focus:ring-2 focus:ring-indigo-500" />
          </div>
          <?php endforeach; ?>
          <p class="text-sm font-semibold text-gray-700 mt-4">Stripe</p>
          <?php foreach (['stripe_public_key'=>'Public key','stripe_secret_key'=>'Secret key'] as $k=>$l): ?>
          <div>
            <label class="block text-sm font-medium text-gray-700 mb-1.5"><?= $l ?></label>
            <input type="text" name="<?= $k ?>" value="<?= htmlspecialchars(get_setting($db,$k)) ?>" class="w-full px-4 py-3 border border-gray-200 rounded-xl text-sm focus:outline-none focus:ring-2 focus:ring-indigo-500" />
          </div>
          <?php endforeach; ?>
          <p class="text-sm font-semibold text-gray-700 mt-4">PayPal</p>
          <?php foreach (['paypal_client_id'=>'Client ID','paypal_client_secret'=>'Client secret'] as $k=>$l): ?>
          <div>
            <label class="block text-sm font-medium text-gray-700 mb-1.5"><?= $l ?></label>
            <input type="text" name="<?= $k ?>" value="<?= htmlspecialchars(get_setting($db,$k)) ?>" class="w-full px-4 py-3 border border-gray-200 rounded-xl text-sm focus:outline-none focus:ring-2 focus:ring-indigo-500" />
          </div>
          <?php endforeach; ?>
          <button type="submit" class="bg-indigo-600 text-white px-6 py-3 rounded-xl text-sm font-medium hover:bg-indigo-700 flex items-center gap-2"><i data-lucide="save" class="w-4 h-4"></i> Save Payments</button>
        </form>

        <?php elseif ($tab === 'ai'): ?>
        <h3 class="font-semibold text-gray-900 mb-5 flex items-center gap-2"><i data-lucide="zap" class="w-5 h-5 text-indigo-600"></i> LearnPulse AI Settings</h3>
        <form method="POST" class="space-y-4">
          <input type="hidden" name="section" value="ai">
          <label class="flex items-center gap-3 cursor-pointer p-3 border border-gray-200 rounded-xl hover:bg-gray-50">
            <input type="checkbox" name="learnpulse_enabled" value="1" <?= get_setting($db,'learnpulse_enabled')==='1'?'checked':'' ?> class="w-4 h-4 text-indigo-600 rounded">
            <span class="text-sm font-medium text-gray-700">Enable LearnPulse AI Tutor</span>
          </label>
          <div>
            <label class="block text-sm font-medium text-gray-700 mb-1.5">AI Provider</label>
            <select name="learnpulse_provider" class="w-full px-4 py-3 border border-gray-200 rounded-xl text-sm focus:outline-none focus:ring-2 focus:ring-indigo-500">
              <?php foreach (['gemini','grok','huggingface'] as $p): ?>
              <option value="<?= $p ?>" <?= get_setting($db,'learnpulse_provider')===$p?'selected':'' ?>><?= ucfirst($p) ?></option>
              <?php endforeach; ?>
            </select>
          </div>
          <?php foreach (['gemini_api_key'=>'Google Gemini API key','grok_api_key'=>'Grok API key'] as $k=>$l): ?>
          <div>
            <label class="block text-sm font-medium text-gray-700 mb-1.5"><?= $l ?></label>
            <input type="text" name="<?= $k ?>" value="<?= htmlspecialchars(get_setting($db,$k)) ?>" class="w-full px-4 py-3 border border-gray-200 rounded-xl text-sm focus:outline-none focus:ring-2 focus:ring-indigo-500" />
          </div>
          <?php endforeach; ?>
          <button type="submit" class="bg-indigo-600 text-white px-6 py-3 rounded-xl text-sm font-medium hover:bg-indigo-700 flex items-center gap-2"><i data-lucide="save" class="w-4 h-4"></i> Save AI Settings</button>
        </form>

        <?php elseif ($tab === 'maintenance'): ?>
        <h3 class="font-semibold text-gray-900 mb-5 flex items-center gap-2">
          <i data-lucide="wrench" class="w-5 h-5 text-indigo-600"></i> Maintenance Mode
        </h3>
        <div class="bg-red-50 border border-red-200 rounded-xl p-4 flex gap-3 mb-4">
          <i data-lucide="alert-triangle" class="w-5 h-5 text-red-500 flex-shrink-0"></i>
          <p class="text-sm text-red-700">When enabled, all users will see the maintenance page. Admins can still login.</p>
        </div>
        <form method="POST" class="space-y-4">
          <input type="hidden" name="section" value="maintenance">
          <label class="flex items-center gap-3 cursor-pointer p-4 border border-gray-200 rounded-xl hover:bg-gray-50">
            <input type="checkbox" name="maintenance_mode" value="1" <?= get_setting($db,'maintenance_mode')==='1'?'checked':'' ?> class="w-4 h-4 text-red-600 rounded">
            <span class="text-sm font-medium text-gray-700">Enable maintenance mode</span>
          </label>
          <div>
            <label class="block text-sm font-medium text-gray-700 mb-1.5">Maintenance message</label>
            <textarea name="maintenance_message" rows="3" class="w-full px-4 py-3 border border-gray-200 rounded-xl text-sm focus:outline-none focus:ring-2 focus:ring-indigo-500 resize-none"><?= htmlspecialchars(get_setting($db,'maintenance_message')) ?></textarea>
          </div>
          <button type="submit" class="bg-indigo-600 text-white px-6 py-3 rounded-xl text-sm font-medium hover:bg-indigo-700 flex items-center gap-2">
            <i data-lucide="save" class="w-4 h-4"></i> Save
          </button>
        </form>

        <?php elseif ($tab === 'social'): ?>
        <h3 class="font-semibold text-gray-900 mb-5 flex items-center gap-2">
          <i data-lucide="share-2" class="w-5 h-5 text-indigo-600"></i> Social Links
        </h3>
        <p class="text-sm text-gray-500 mb-5">These links appear as icons in all emails sent from ByteClass. Leave blank to hide an icon.</p>
        <form method="POST" class="space-y-4">
          <input type="hidden" name="section" value="social">
          <?php
          $social_fields = [
            ['key'=>'social_website',  'label'=>'Website URL',     'ph'=>'https://byteclass.io',            'icon'=>'globe'],
            ['key'=>'social_whatsapp', 'label'=>'WhatsApp link',   'ph'=>'https://wa.me/254700000000',      'icon'=>'message-circle'],
            ['key'=>'social_telegram', 'label'=>'Telegram link',   'ph'=>'https://t.me/byteclass',         'icon'=>'send'],
            ['key'=>'social_twitter',  'label'=>'X / Twitter URL', 'ph'=>'https://x.com/byteclass',        'icon'=>'twitter'],
            ['key'=>'social_facebook', 'label'=>'Facebook URL',    'ph'=>'https://facebook.com/byteclass', 'icon'=>'facebook'],
            ['key'=>'social_instagram','label'=>'Instagram URL',   'ph'=>'https://instagram.com/byteclass','icon'=>'instagram'],
          ];
          foreach ($social_fields as $f): ?>
          <div>
            <label class="block text-sm font-medium text-gray-700 mb-1.5 flex items-center gap-2">
              <i data-lucide="<?= $f['icon'] ?>" class="w-4 h-4 text-gray-400"></i>
              <?= $f['label'] ?>
            </label>
            <input type="url" name="<?= $f['key'] ?>"
              value="<?= htmlspecialchars(get_setting($db, $f['key'])) ?>"
              placeholder="<?= $f['ph'] ?>"
              class="w-full px-4 py-3 border border-gray-200 rounded-xl text-sm focus:outline-none focus:ring-2 focus:ring-indigo-500" />
          </div>
          <?php endforeach; ?>
          <button type="submit" class="bg-indigo-600 text-white px-6 py-3 rounded-xl text-sm font-medium hover:bg-indigo-700 flex items-center gap-2">
            <i data-lucide="save" class="w-4 h-4"></i> Save Social Links
          </button>
        </form>
        <?php endif; ?>

      </div>
    </main>
  </div>
</div>

<?php require_once $_SERVER['DOCUMENT_ROOT'] . '/ByteClass/includes/scripts.php'; ?>
<script>
function togglePwd(inputId, iconId) {
  const input = document.getElementById(inputId);
  const icon  = document.getElementById(iconId);
  if (input.type === 'password') {
    input.type = 'text';
    icon.setAttribute('data-lucide', 'eye-off');
  } else {
    input.type = 'password';
    icon.setAttribute('data-lucide', 'eye');
  }
  lucide.createIcons();
}

function previewPhoto(input) {
  if (!input.files || !input.files[0]) return;
  const reader = new FileReader();
  reader.onload = (e) => {
    const preview = document.getElementById('photo-preview');
    preview.innerHTML = '<img src="' + e.target.result + '" class="w-full h-full object-cover" />';
  };
  reader.readAsDataURL(input.files[0]);
}

function checkMatch() {
  const np   = document.getElementById('pwd-new').value;
  const cp   = document.getElementById('pwd-confirm').value;
  const msg  = document.getElementById('match-msg');
  if (!cp) { msg.classList.add('hidden'); return; }
  msg.classList.remove('hidden');
  if (np === cp) {
    msg.textContent = '✓ Passwords match';
    msg.className   = 'text-xs mt-1 text-green-600';
  } else {
    msg.textContent = '✗ Passwords do not match';
    msg.className   = 'text-xs mt-1 text-red-500';
  }
}
</script>
<?php require_once $_SERVER['DOCUMENT_ROOT'] . '/ByteClass/includes/footer-minimal.php'; ?>