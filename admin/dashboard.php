<?php
$required_role = 'admin';
require_once $_SERVER['DOCUMENT_ROOT'] . '/ByteClass/includes/auth-check.php';
require_once $_SERVER['DOCUMENT_ROOT'] . '/ByteClass/config/constants.php';
require_once $_SERVER['DOCUMENT_ROOT'] . '/ByteClass/config/database.php';

$db = Database::getInstance()->getConnection();

$total_students   = $db->query("SELECT COUNT(*) FROM users WHERE role='student' AND status='active'")->fetchColumn();
$total_lecturers  = $db->query("SELECT COUNT(*) FROM users WHERE role='lecturer' AND status='active'")->fetchColumn();
$total_courses    = $db->query("SELECT COUNT(*) FROM courses WHERE status='published'")->fetchColumn();
$revenue_month    = $db->query("SELECT COALESCE(SUM(amount),0) FROM payments WHERE status='success' AND MONTH(confirmed_at)=MONTH(NOW())")->fetchColumn();
$pending_approvals= $db->query("SELECT COUNT(*) FROM leave_requests WHERE status='pending'")->fetchColumn();
$failed_payments  = $db->query("SELECT COUNT(*) FROM payments WHERE status='failed'")->fetchColumn();
$locked_accounts  = $db->query("SELECT COUNT(*) FROM users WHERE status='locked'")->fetchColumn();
$open_tickets     = $db->query("SELECT COUNT(*) FROM support_tickets WHERE status='open'")->fetchColumn();

$recent_users = $db->query("SELECT id, full_name, email, role, status, created_at, profile_photo FROM users ORDER BY created_at DESC LIMIT 5")->fetchAll();
$recent_payments = $db->query("SELECT p.receipt_id, p.amount, p.currency, p.gateway, p.status, p.initiated_at, u.full_name FROM payments p JOIN users u ON p.student_id = u.id ORDER BY p.initiated_at DESC LIMIT 5")->fetchAll();

$page_title = 'Admin Dashboard';
require_once $_SERVER['DOCUMENT_ROOT'] . '/ByteClass/includes/head.php';
require_once $_SERVER['DOCUMENT_ROOT'] . '/ByteClass/config/constants.php';
?>

<!-- Dashboard wrapper -->
<div class="flex min-h-screen bg-gray-50">

  <?php require_once $_SERVER['DOCUMENT_ROOT'] . '/ByteClass/includes/sidebar-admin.php'; ?>

  <!-- Main content -->
  <div id="main-content" class="flex-1 flex flex-col" style="margin-left:256px;">

    <!-- Top navbar (replaced custom header) -->
    <?php require_once $_SERVER['DOCUMENT_ROOT'] . '/ByteClass/includes/navbar-admin.php'; ?>

    <!-- Page content -->
    <main class="flex-1 p-6">

      <!-- Welcome bar -->
      <div class="bg-gradient-to-r from-indigo-600 to-cyan-500 rounded-2xl px-6 py-5 mb-6 flex items-center justify-between">
        <div>
          <h2 class="text-white text-xl font-bold">
            Welcome back, <?= htmlspecialchars(explode(' ', $_SESSION['full_name'])[0]) ?> 👋
          </h2>
          <p class="text-indigo-100 text-sm mt-0.5">System Administrator — ByteClass</p>
        </div>
        <div class="w-14 h-14 bg-white bg-opacity-20 rounded-2xl flex items-center justify-center text-white text-xl font-bold">
          <?= strtoupper(substr($_SESSION['full_name'], 0, 1)) ?>
        </div>
      </div>

      <!-- Stats grid -->
      <div class="grid grid-cols-2 lg:grid-cols-4 gap-4 mb-6">
        <?php
        $stats = [
          ['label' => 'Total students',    'value' => number_format($total_students),  'icon' => 'users',          'color' => 'bg-indigo-500',  'link' => 'users.php'],
          ['label' => 'Total lecturers',   'value' => number_format($total_lecturers), 'icon' => 'graduation-cap', 'color' => 'bg-cyan-500',    'link' => 'users.php'],
          ['label' => 'Active courses',    'value' => number_format($total_courses),   'icon' => 'book-open',      'color' => 'bg-green-500',   'link' => 'courses.php'],
          ['label' => 'Revenue this month','value' => 'KES ' . number_format($revenue_month), 'icon' => 'dollar-sign', 'color' => 'bg-emerald-500', 'link' => 'finance.php'],
          ['label' => 'Pending approvals', 'value' => number_format($pending_approvals),'icon' => 'clock',         'color' => 'bg-amber-500',   'link' => 'hr.php'],
          ['label' => 'Failed payments',   'value' => number_format($failed_payments), 'icon' => 'x-circle',      'color' => 'bg-red-500',     'link' => 'finance.php'],
          ['label' => 'Locked accounts',   'value' => number_format($locked_accounts), 'icon' => 'lock',          'color' => 'bg-rose-500',    'link' => 'users.php'],
          ['label' => 'Open tickets',      'value' => number_format($open_tickets),    'icon' => 'headphones',    'color' => 'bg-purple-500',  'link' => 'support.php'],
        ];
        foreach ($stats as $stat): ?>
        <a href="<?= APP_URL ?>/admin/<?= $stat['link'] ?>"
           class="bg-white rounded-2xl p-5 border border-gray-100 flex items-center gap-4 dash-card hover:no-underline">
          <div class="w-12 h-12 <?= $stat['color'] ?> rounded-xl flex items-center justify-center flex-shrink-0">
            <i data-lucide="<?= $stat['icon'] ?>" class="w-6 h-6 text-white"></i>
          </div>
          <div>
            <p class="text-2xl font-bold text-gray-900"><?= $stat['value'] ?></p>
            <p class="text-xs text-gray-500 mt-0.5"><?= $stat['label'] ?></p>
          </div>
        </a>
        <?php endforeach; ?>
      </div>

      <!-- Two column grid -->
      <div class="grid grid-cols-1 lg:grid-cols-2 gap-6">

        <!-- Recent users -->
        <div class="bg-white rounded-2xl border border-gray-100 overflow-hidden">
          <div class="px-5 py-4 border-b border-gray-100 flex items-center justify-between">
            <h3 class="font-semibold text-gray-900 flex items-center gap-2">
              <i data-lucide="users" class="w-4 h-4 text-indigo-600"></i>
              Recent registrations
            </h3>
            <a href="<?= APP_URL ?>/admin/users.php" class="text-xs text-indigo-600 hover:underline">View all</a>
          </div>
          <div class="divide-y divide-gray-50">
            <?php if (empty($recent_users)): ?>
              <div class="px-5 py-8 text-center text-gray-400 text-sm">No users yet</div>
            <?php else: foreach ($recent_users as $u): ?>
            <div class="px-5 py-3 flex items-center gap-3">
              <?php if (!empty($u['profile_photo'])): ?>
              <img src="<?= htmlspecialchars($u['profile_photo']) ?>" alt="Photo"
                   class="w-9 h-9 rounded-full object-cover flex-shrink-0 border border-gray-200" />
              <?php else: ?>
              <div class="w-9 h-9 rounded-full flex items-center justify-center text-white text-sm font-bold flex-shrink-0
                <?= $u['role'] === 'admin' ? 'bg-indigo-500' : ($u['role'] === 'lecturer' ? 'bg-cyan-500' : 'bg-green-500') ?>">
                <?= strtoupper(substr($u['full_name'], 0, 1)) ?>
              </div>
              <?php endif; ?>
              <div class="flex-1 min-w-0">
                <p class="text-sm font-medium text-gray-900 truncate"><?= htmlspecialchars($u['full_name']) ?></p>
                <p class="text-xs text-gray-400 truncate"><?= htmlspecialchars($u['email']) ?></p>
              </div>
              <div class="flex flex-col items-end gap-1">
                <span class="text-xs px-2 py-0.5 rounded-full font-medium
                  <?= $u['role'] === 'admin' ? 'bg-indigo-100 text-indigo-700' : ($u['role'] === 'lecturer' ? 'bg-cyan-100 text-cyan-700' : 'bg-green-100 text-green-700') ?>">
                  <?= ucfirst($u['role']) ?>
                </span>
                <span class="text-xs text-gray-400"><?= date('M d', strtotime($u['created_at'])) ?></span>
              </div>
            </div>
            <?php endforeach; endif; ?>
          </div>
        </div>

        <!-- Recent payments -->
        <div class="bg-white rounded-2xl border border-gray-100 overflow-hidden">
          <div class="px-5 py-4 border-b border-gray-100 flex items-center justify-between">
            <h3 class="font-semibold text-gray-900 flex items-center gap-2">
              <i data-lucide="credit-card" class="w-4 h-4 text-green-600"></i>
              Recent payments
            </h3>
            <a href="<?= APP_URL ?>/admin/finance.php" class="text-xs text-indigo-600 hover:underline">View all</a>
          </div>
          <div class="divide-y divide-gray-50">
            <?php if (empty($recent_payments)): ?>
              <div class="px-5 py-8 text-center text-gray-400 text-sm">No payments yet</div>
            <?php else: foreach ($recent_payments as $p): ?>
            <div class="px-5 py-3 flex items-center gap-3">
              <div class="w-9 h-9 bg-gray-100 rounded-xl flex items-center justify-center flex-shrink-0">
                <i data-lucide="credit-card" class="w-4 h-4 text-gray-500"></i>
              </div>
              <div class="flex-1 min-w-0">
                <p class="text-sm font-medium text-gray-900 truncate"><?= htmlspecialchars($p['full_name']) ?></p>
                <p class="text-xs text-gray-400"><?= strtoupper($p['gateway']) ?> · <?= $p['receipt_id'] ?></p>
              </div>
              <div class="flex flex-col items-end gap-1">
                <span class="text-sm font-bold <?= $p['status'] === 'success' ? 'text-green-600' : 'text-red-500' ?>">
                  <?= $p['currency'] ?> <?= number_format($p['amount'], 2) ?>
                </span>
                <span class="text-xs px-2 py-0.5 rounded-full font-medium
                  <?= $p['status'] === 'success' ? 'bg-green-100 text-green-700' : 'bg-red-100 text-red-700' ?>">
                  <?= ucfirst($p['status']) ?>
                </span>
              </div>
            </div>
            <?php endforeach; endif; ?>
          </div>
        </div>

      </div>
    </main>
  </div>
</div>

<?php
require_once $_SERVER['DOCUMENT_ROOT'] . '/ByteClass/includes/scripts.php';
require_once $_SERVER['DOCUMENT_ROOT'] . '/ByteClass/includes/footer-minimal.php';
?>