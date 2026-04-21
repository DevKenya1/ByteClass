<?php
$notif_count  = 0;
$notifications = [];
try {
    $db_nav = Database::getInstance()->getConnection();
    $ns = $db_nav->prepare("SELECT id,title,message,type,link,is_read,created_at FROM notifications WHERE user_id=? ORDER BY created_at DESC LIMIT 15");
    $ns->execute([$_SESSION['user_id']]);
    $notifications = $ns->fetchAll();
    $notif_count = count(array_filter($notifications, fn($n) => !$n['is_read']));
} catch (Exception $e) {}
$photo     = $_SESSION['photo'] ?? null;
$initials  = strtoupper(substr($_SESSION['full_name'] ?? 'S', 0, 1));
$firstname = htmlspecialchars(explode(' ', $_SESSION['full_name'])[0]);
?>
<header class="bg-white border-b border-gray-200 px-6 py-3 flex items-center justify-between sticky top-0 z-30">
  <div class="flex items-center gap-3">
    <button onclick="toggleSidebar()" class="text-gray-500 hover:text-gray-700 p-1.5 rounded-lg hover:bg-gray-100">
      <i data-lucide="menu" class="w-5 h-5"></i>
    </button>
    <h1 class="text-lg font-semibold text-gray-800"><?= $page_title ?? 'Dashboard' ?></h1>
  </div>
  <div class="flex items-center gap-3">
    <div class="relative" data-dropdown="notif-dropdown">
      <button onclick="toggleDropdown('notif-dropdown'); markNotifsRead()"
        class="relative p-2 text-gray-500 hover:text-indigo-600 hover:bg-indigo-50 rounded-xl transition-colors">
        <i data-lucide="bell" class="w-5 h-5"></i>
        <?php if ($notif_count > 0): ?>
        <span id="notif-badge" class="absolute -top-0.5 -right-0.5 w-5 h-5 bg-red-500 text-white text-xs rounded-full flex items-center justify-center font-bold">
          <?= $notif_count > 9 ? '9+' : $notif_count ?>
        </span>
        <?php else: ?>
        <span id="notif-badge" class="hidden absolute -top-0.5 -right-0.5 w-5 h-5 bg-red-500 text-white text-xs rounded-full flex items-center justify-center font-bold"></span>
        <?php endif; ?>
      </button>
      <div id="notif-dropdown" class="hidden absolute right-0 top-12 w-80 bg-white border border-gray-100 rounded-2xl shadow-xl z-50 overflow-hidden">
        <div class="px-4 py-3 border-b border-gray-100 flex items-center justify-between">
          <h3 class="font-semibold text-gray-900 text-sm">Notifications</h3>
          <?php if ($notif_count > 0): ?>
          <button onclick="markNotifsRead()" class="text-xs text-indigo-600 hover:underline">Mark all read</button>
          <?php endif; ?>
        </div>
        <div class="max-h-72 overflow-y-auto">
          <?php if (empty($notifications)): ?>
          <div class="px-4 py-8 text-center">
            <i data-lucide="bell-off" class="w-8 h-8 text-gray-300 mx-auto mb-2"></i>
            <p class="text-sm text-gray-400">No notifications yet</p>
          </div>
          <?php else: foreach ($notifications as $n): ?>
          <div class="px-4 py-3 hover:bg-gray-50 <?= !$n['is_read'] ? 'bg-indigo-50' : '' ?> border-b border-gray-50">
            <div class="flex items-start gap-3">
              <div class="w-7 h-7 bg-indigo-100 rounded-lg flex items-center justify-center flex-shrink-0 mt-0.5">
                <i data-lucide="bell" class="w-3.5 h-3.5 text-indigo-600"></i>
              </div>
              <div class="flex-1 min-w-0">
                <p class="text-sm font-medium text-gray-900 leading-tight"><?= htmlspecialchars($n['title']) ?></p>
                <p class="text-xs text-gray-500 mt-0.5"><?= htmlspecialchars($n['message']) ?></p>
                <p class="text-xs text-gray-400 mt-1"><?= time_ago($n['created_at']) ?></p>
              </div>
              <?php if (!$n['is_read']): ?>
              <div class="w-2 h-2 bg-indigo-500 rounded-full flex-shrink-0 mt-2"></div>
              <?php endif; ?>
            </div>
          </div>
          <?php endforeach; endif; ?>
        </div>
      </div>
    </div>

    <div class="relative" data-dropdown="profile-menu">
      <button onclick="toggleDropdown('profile-menu')" class="flex items-center gap-2 hover:bg-gray-50 rounded-xl px-2 py-1.5">
        <?php if ($photo): ?>
        <img src="<?= htmlspecialchars($photo) ?>" alt="Profile" class="w-8 h-8 rounded-full object-cover border-2 border-indigo-200" />
        <?php else: ?>
        <div class="w-8 h-8 bg-indigo-600 rounded-full flex items-center justify-center text-white text-xs font-bold"><?= $initials ?></div>
        <?php endif; ?>
        <div class="hidden sm:block text-left">
          <p class="text-sm font-medium text-gray-800 leading-tight"><?= $firstname ?></p>
          <p class="text-xs text-gray-400 leading-tight">Student</p>
        </div>
      </button>
      <div id="profile-menu" class="hidden absolute right-0 top-12 w-48 bg-white border border-gray-100 rounded-xl shadow-lg py-1 z-50">
        <a href="<?= APP_URL ?>/student/profile.php" class="flex items-center gap-3 px-4 py-2.5 text-sm text-gray-700 hover:bg-gray-50">
          <i data-lucide="user" class="w-4 h-4 text-gray-400"></i> My profile
        </a>
        <div class="border-t border-gray-100">
          <a href="<?= APP_URL ?>/api/auth/logout.php" class="flex items-center gap-3 px-4 py-2.5 text-sm text-red-600 hover:bg-red-50">
            <i data-lucide="log-out" class="w-4 h-4"></i> Logout
          </a>
        </div>
      </div>
    </div>
  </div>
</header>
<script>
function markNotifsRead() {
  document.getElementById('notif-badge')?.classList.add('hidden');
  fetch('<?= APP_URL ?>/api/notifications/mark_all_read.php', {method:'POST'}).catch(()=>{});
}
</script>
