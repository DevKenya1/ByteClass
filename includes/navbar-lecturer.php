<?php
$_notif_count   = 0;
$_notifications = [];
try {
    $db_nav = Database::getInstance()->getConnection();
    $ns = $db_nav->prepare("SELECT id,title,message,type,link,is_read,created_at FROM notifications WHERE user_id=? ORDER BY created_at DESC LIMIT 15");
    $ns->execute([$_SESSION['user_id']]);
    $_notifications  = $ns->fetchAll();
    $_notif_count = count(array_filter($_notifications, fn($n) => !$n['is_read']));
} catch (Exception $e) {}
$_photo     = $_SESSION['photo'] ?? null;
$_initials  = strtoupper(substr($_SESSION['full_name'] ?? 'L', 0, 1));
$_firstname = htmlspecialchars(explode(' ', $_SESSION['full_name'])[0]);
?>
<header class="bg-white border-b border-gray-200 px-6 py-3 flex items-center justify-between sticky top-0 z-30">
  <div class="flex items-center gap-3">
    <button onclick="toggleSidebar()" class="text-gray-500 hover:text-gray-700 p-1.5 rounded-lg hover:bg-gray-100">
      <i data-lucide="menu" class="w-5 h-5"></i>
    </button>
    <h1 class="text-lg font-semibold text-gray-800"><?= $page_title ?? 'Lecturer' ?></h1>
  </div>
  <div class="flex items-center gap-3">
    <!-- Bell -->
    <div class="relative" data-dropdown="lec-notif-dd">
      <button onclick="toggleDropdown('lec-notif-dd'); lecMarkRead()"
        class="relative p-2 text-gray-500 hover:text-indigo-600 hover:bg-indigo-50 rounded-xl transition-colors">
        <i data-lucide="bell" class="w-5 h-5"></i>
        <span id="lec-notif-badge"
          class="<?= $_notif_count > 0 ? '' : 'hidden' ?> absolute -top-0.5 -right-0.5 w-5 h-5 bg-red-500 text-white text-xs rounded-full flex items-center justify-center font-bold">
          <?= $_notif_count > 9 ? '9+' : $_notif_count ?>
        </span>
      </button>
      <div id="lec-notif-dd" class="hidden absolute right-0 top-12 w-80 bg-white border border-gray-100 rounded-2xl shadow-xl z-50 overflow-hidden">
        <div class="px-4 py-3 border-b border-gray-100 flex items-center justify-between">
          <h3 class="font-semibold text-gray-900 text-sm">Notifications</h3>
          <?php if ($_notif_count > 0): ?>
          <button onclick="lecMarkRead()" class="text-xs text-indigo-600 hover:underline">Mark all read</button>
          <?php endif; ?>
        </div>
        <div class="max-h-72 overflow-y-auto">
          <?php if (empty($_notifications)): ?>
          <div class="px-4 py-8 text-center">
            <i data-lucide="bell-off" class="w-8 h-8 text-gray-300 mx-auto mb-2"></i>
            <p class="text-sm text-gray-400">No notifications yet</p>
          </div>
          <?php else: foreach ($_notifications as $_n):
            $_tc = match($_n['type'] ?? 'general') { 'hr'=>'bg-blue-100 text-blue-600','support'=>'bg-purple-100 text-purple-600','review'=>'bg-amber-100 text-amber-600','payment'=>'bg-green-100 text-green-600',default=>'bg-gray-100 text-gray-600' };
            $_ti = match($_n['type'] ?? 'general') { 'hr'=>'briefcase','support'=>'headphones','review'=>'star','payment'=>'dollar-sign',default=>'bell' };
          ?>
          <div class="px-4 py-3 hover:bg-gray-50 border-b border-gray-50 <?= !$_n['is_read'] ? 'bg-indigo-50' : '' ?>">
            <div class="flex items-start gap-3">
              <div class="w-8 h-8 <?= $_tc ?> rounded-xl flex items-center justify-center flex-shrink-0 mt-0.5">
                <i data-lucide="<?= $_ti ?>" class="w-4 h-4"></i>
              </div>
              <div class="flex-1 min-w-0">
                <p class="text-sm font-medium text-gray-900 leading-tight"><?= htmlspecialchars($_n['title']) ?></p>
                <p class="text-xs text-gray-500 mt-0.5"><?= htmlspecialchars($_n['message']) ?></p>
                <p class="text-xs text-gray-400 mt-1"><?= time_ago($_n['created_at']) ?></p>
              </div>
              <?php if (!$_n['is_read']): ?><div class="w-2 h-2 bg-indigo-500 rounded-full flex-shrink-0 mt-2"></div><?php endif; ?>
            </div>
          </div>
          <?php endforeach; endif; ?>
        </div>
      </div>
    </div>
    <!-- Profile -->
    <div class="relative" data-dropdown="lec-profile-menu">
      <button onclick="toggleDropdown('lec-profile-menu')" class="flex items-center gap-2 hover:bg-gray-50 rounded-xl px-2 py-1.5 transition-colors">
        <?php if ($_photo): ?>
        <img src="<?= htmlspecialchars($_photo) ?>" class="w-8 h-8 rounded-full object-cover border-2 border-cyan-200 flex-shrink-0" />
        <?php else: ?>
        <div class="w-8 h-8 bg-cyan-600 rounded-full flex items-center justify-center text-white text-xs font-bold flex-shrink-0"><?= $_initials ?></div>
        <?php endif; ?>
        <div class="hidden sm:block text-left">
          <p class="text-sm font-medium text-gray-800 leading-tight"><?= $_firstname ?></p>
          <p class="text-xs text-gray-400 leading-tight">Lecturer</p>
        </div>
        <i data-lucide="chevron-down" class="w-4 h-4 text-gray-400"></i>
      </button>
      <div id="lec-profile-menu" class="hidden absolute right-0 top-12 w-52 bg-white border border-gray-100 rounded-xl shadow-lg py-1 z-50">
        <div class="px-4 py-3 border-b border-gray-100">
          <p class="text-sm font-medium text-gray-800 truncate"><?= htmlspecialchars($_SESSION['full_name']) ?></p>
          <p class="text-xs text-gray-400 truncate"><?= htmlspecialchars($_SESSION['email']) ?></p>
        </div>
        <a href="<?= APP_URL ?>/lecturer/profile.php" class="flex items-center gap-3 px-4 py-2.5 text-sm text-gray-700 hover:bg-gray-50">
          <i data-lucide="user" class="w-4 h-4 text-gray-400"></i> My Profile
        </a>
        <a href="<?= APP_URL ?>/lecturer/payslips.php" class="flex items-center gap-3 px-4 py-2.5 text-sm text-gray-700 hover:bg-gray-50">
          <i data-lucide="file-text" class="w-4 h-4 text-gray-400"></i> My Payslips
        </a>
        <div class="border-t border-gray-100 mt-1">
          <a href="<?= APP_URL ?>/api/auth/logout.php" class="flex items-center gap-3 px-4 py-2.5 text-sm text-red-600 hover:bg-red-50">
            <i data-lucide="log-out" class="w-4 h-4"></i> Logout
          </a>
        </div>
      </div>
    </div>
  </div>
</header>
<script>
function lecMarkRead() {
  document.getElementById('lec-notif-badge')?.classList.add('hidden');
  fetch('<?= APP_URL ?>/api/notifications/mark_all_read.php', {method:'POST'}).catch(()=>{});
}
</script>
