<?php
$required_role = 'lecturer';
require_once $_SERVER['DOCUMENT_ROOT'] . '/ByteClass/includes/auth-check.php';
require_once $_SERVER['DOCUMENT_ROOT'] . '/ByteClass/config/constants.php';
require_once $_SERVER['DOCUMENT_ROOT'] . '/ByteClass/config/database.php';
require_once $_SERVER['DOCUMENT_ROOT'] . '/ByteClass/helpers/response.php';
require_once $_SERVER['DOCUMENT_ROOT'] . '/ByteClass/helpers/upload.php';
$db = Database::getInstance()->getConnection();
$id = (int)$_SESSION['user_id'];
$success_msg = $error_msg = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $action = $_POST['action'] ?? '';
    if ($action === 'update_profile') {
        $full_name = sanitize($_POST['full_name'] ?? '');
        $phone     = sanitize($_POST['phone']     ?? '');
        $address   = sanitize($_POST['address']   ?? '');
        $bio       = sanitize($_POST['bio']       ?? '');
        $new_email = strtolower(trim($_POST['email'] ?? ''));
        if (!$full_name) { $error_msg='Full name required.'; }
        else {
            $photo_url = null;
            if (isset($_FILES['profile_photo']) && $_FILES['profile_photo']['error']===0) {
                $photo_url = upload_profile_photo($_FILES['profile_photo'], 'lec_'.$id);
                if (!$photo_url) $error_msg = 'Invalid image.';
            }
            if (!$error_msg) {
                if ($new_email && $new_email !== $_SESSION['email']) {
                    $ex = $db->prepare("SELECT id FROM users WHERE email=? AND id!=? LIMIT 1"); $ex->execute([$new_email,$id]);
                    if ($ex->fetch()) $error_msg = 'Email already in use.';
                }
            }
            if (!$error_msg) {
                $email_to_set = ($new_email && $new_email !== $_SESSION['email']) ? $new_email : $_SESSION['email'];
                if ($photo_url) {
                    $db->prepare("UPDATE users SET full_name=?,phone=?,address=?,email=?,profile_photo=?,updated_at=NOW() WHERE id=?")
                       ->execute([$full_name,$phone,$address,$email_to_set,$photo_url,$id]);
                } else {
                    $db->prepare("UPDATE users SET full_name=?,phone=?,address=?,email=?,updated_at=NOW() WHERE id=?")
                       ->execute([$full_name,$phone,$address,$email_to_set,$id]);
                }
                $db->prepare("UPDATE lecturer_profiles SET bio=? WHERE user_id=?")->execute([$bio,$id]);
                $fresh = $db->prepare("SELECT full_name,email,profile_photo FROM users WHERE id=?"); $fresh->execute([$id]); $u=$fresh->fetch();
                $_SESSION['full_name']=$u['full_name']; $_SESSION['email']=$u['email']; $_SESSION['photo']=$u['profile_photo'];
                $success_msg='Profile updated.';
            }
        }
    }
    if ($action === 'change_password') {
        $current  = $_POST['current_password'] ?? '';
        $new_pass = $_POST['new_password']      ?? '';
        $confirm  = $_POST['confirm_password']  ?? '';
        $hash_row = $db->prepare("SELECT password_hash FROM users WHERE id=?"); $hash_row->execute([$id]); $hash=$hash_row->fetchColumn();
        if (!password_verify($current,$hash)) { $error_msg='Current password incorrect.'; }
        elseif (strlen($new_pass)<8)           { $error_msg='New password must be at least 8 characters.'; }
        elseif ($new_pass!==$confirm)          { $error_msg='Passwords do not match.'; }
        else {
            $db->prepare("UPDATE users SET password_hash=?,updated_at=NOW() WHERE id=?")->execute([password_hash($new_pass,PASSWORD_BCRYPT,['cost'=>12]),$id]);
            $success_msg='Password changed.';
        }
    }
}

$user = $db->prepare("SELECT u.*, lp.department, lp.bio FROM users u LEFT JOIN lecturer_profiles lp ON lp.user_id=u.id WHERE u.id=?");
$user->execute([$id]); $user = $user->fetch();

$page_title = 'My Profile';
require_once $_SERVER['DOCUMENT_ROOT'] . '/ByteClass/includes/head.php';
?>
<div class="flex min-h-screen bg-gray-50">
  <?php require_once $_SERVER['DOCUMENT_ROOT'] . '/ByteClass/includes/sidebar-lecturer.php'; ?>
  <div id="main-content" class="flex-1 flex flex-col" style="margin-left:256px;">
    <?php require_once $_SERVER['DOCUMENT_ROOT'] . '/ByteClass/includes/navbar-lecturer.php'; ?>
    <main class="flex-1 p-6 max-w-2xl">
      <div class="bg-gradient-to-r from-cyan-600 to-indigo-600 rounded-2xl px-6 py-5 mb-6">
        <h2 class="text-white text-xl font-bold">My Profile</h2>
        <p class="text-cyan-100 text-sm mt-0.5">Manage your personal information</p>
      </div>
      <?php if ($success_msg): ?><div class="mb-4 p-4 bg-green-50 border border-green-200 rounded-xl text-sm text-green-700 flex gap-3"><i data-lucide="check-circle" class="w-5 h-5 flex-shrink-0"></i><?= htmlspecialchars($success_msg) ?></div><?php endif; ?>
      <?php if ($error_msg): ?><div class="mb-4 p-4 bg-red-50 border border-red-200 rounded-xl text-sm text-red-700 flex gap-3"><i data-lucide="alert-circle" class="w-5 h-5 flex-shrink-0"></i><?= htmlspecialchars($error_msg) ?></div><?php endif; ?>

      <div class="bg-white rounded-2xl border border-gray-100 p-6 mb-5">
        <h3 class="font-semibold text-gray-900 mb-5 flex items-center gap-2"><i data-lucide="user" class="w-5 h-5 text-cyan-600"></i> Personal Information</h3>
        <form method="POST" enctype="multipart/form-data" class="space-y-4">
          <input type="hidden" name="action" value="update_profile">
          <div class="flex items-center gap-4 mb-4">
            <div class="w-16 h-16 rounded-full overflow-hidden flex-shrink-0 border-2 border-cyan-200 flex items-center justify-center bg-cyan-100" id="photo-preview">
              <?php if ($user['profile_photo']): ?><img src="<?= htmlspecialchars($user['profile_photo']) ?>" class="w-full h-full object-cover" />
              <?php else: ?><span class="text-2xl font-bold text-cyan-600"><?= strtoupper(substr($user['full_name'],0,1)) ?></span><?php endif; ?>
            </div>
            <div>
              <input type="file" name="profile_photo" id="photo-input" accept="image/*" class="hidden" onchange="previewPhoto(this)" />
              <button type="button" onclick="document.getElementById('photo-input').click()" class="px-4 py-2 border border-gray-200 rounded-xl text-sm text-gray-700 hover:bg-gray-50 flex items-center gap-2"><i data-lucide="camera" class="w-4 h-4"></i> Change photo</button>
              <p class="text-xs text-gray-400 mt-1">JPG, PNG or WebP · Max 5MB</p>
            </div>
          </div>
          <div class="grid grid-cols-2 gap-4">
            <div class="col-span-2"><label class="block text-sm font-medium text-gray-700 mb-1.5">Full name *</label><input type="text" name="full_name" value="<?= htmlspecialchars($user['full_name']) ?>" required class="w-full px-4 py-3 border border-gray-200 rounded-xl text-sm focus:outline-none focus:ring-2 focus:ring-indigo-500" /></div>
            <div><label class="block text-sm font-medium text-gray-700 mb-1.5">Email</label><input type="email" name="email" value="<?= htmlspecialchars($user['email']) ?>" class="w-full px-4 py-3 border border-gray-200 rounded-xl text-sm focus:outline-none focus:ring-2 focus:ring-indigo-500" /></div>
            <div><label class="block text-sm font-medium text-gray-700 mb-1.5">Phone</label><input type="tel" name="phone" value="<?= htmlspecialchars($user['phone']) ?>" class="w-full px-4 py-3 border border-gray-200 rounded-xl text-sm focus:outline-none focus:ring-2 focus:ring-indigo-500" /></div>
            <div class="col-span-2"><label class="block text-sm font-medium text-gray-700 mb-1.5">Address</label><input type="text" name="address" value="<?= htmlspecialchars($user['address']) ?>" class="w-full px-4 py-3 border border-gray-200 rounded-xl text-sm focus:outline-none focus:ring-2 focus:ring-indigo-500" /></div>
            <div class="col-span-2"><label class="block text-sm font-medium text-gray-700 mb-1.5">Bio / About me</label><textarea name="bio" rows="3" class="w-full px-4 py-3 border border-gray-200 rounded-xl text-sm focus:outline-none focus:ring-2 focus:ring-indigo-500 resize-none" placeholder="Tell students about yourself..."><?= htmlspecialchars($user['bio'] ?? '') ?></textarea></div>
          </div>
          <button type="submit" class="bg-cyan-600 text-white px-6 py-3 rounded-xl text-sm font-semibold hover:bg-cyan-700 flex items-center gap-2"><i data-lucide="save" class="w-4 h-4"></i> Save Profile</button>
        </form>
      </div>

      <div class="bg-white rounded-2xl border border-gray-100 p-6">
        <h3 class="font-semibold text-gray-900 mb-5 flex items-center gap-2"><i data-lucide="lock" class="w-5 h-5 text-cyan-600"></i> Change Password</h3>
        <form method="POST" class="space-y-4">
          <input type="hidden" name="action" value="change_password">
          <div><label class="block text-sm font-medium text-gray-700 mb-1.5">Current password</label><input type="password" name="current_password" required class="w-full px-4 py-3 border border-gray-200 rounded-xl text-sm focus:outline-none focus:ring-2 focus:ring-indigo-500" /></div>
          <div class="grid grid-cols-2 gap-4">
            <div><label class="block text-sm font-medium text-gray-700 mb-1.5">New password</label><input type="password" name="new_password" required minlength="8" class="w-full px-4 py-3 border border-gray-200 rounded-xl text-sm focus:outline-none focus:ring-2 focus:ring-indigo-500" /></div>
            <div><label class="block text-sm font-medium text-gray-700 mb-1.5">Confirm</label><input type="password" name="confirm_password" required class="w-full px-4 py-3 border border-gray-200 rounded-xl text-sm focus:outline-none focus:ring-2 focus:ring-indigo-500" /></div>
          </div>
          <button type="submit" class="bg-rose-600 text-white px-6 py-3 rounded-xl text-sm font-semibold hover:bg-rose-700 flex items-center gap-2"><i data-lucide="key" class="w-4 h-4"></i> Change Password</button>
        </form>
      </div>
    </main>
    <?php require_once $_SERVER['DOCUMENT_ROOT'] . '/ByteClass/includes/footer-minimal.php'; ?>
  </div>
</div>
<?php require_once $_SERVER['DOCUMENT_ROOT'] . '/ByteClass/includes/scripts.php'; ?>
<script>
function previewPhoto(input) {
  if (!input.files||!input.files[0]) return;
  const r = new FileReader();
  r.onload = e => { document.getElementById('photo-preview').innerHTML = '<img src="'+e.target.result+'" class="w-full h-full object-cover" />'; };
  r.readAsDataURL(input.files[0]);
}
</script>
