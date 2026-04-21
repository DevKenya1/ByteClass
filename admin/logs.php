<?php
$required_role = 'admin';
require_once $_SERVER['DOCUMENT_ROOT'] . '/ByteClass/includes/auth-check.php';
require_once $_SERVER['DOCUMENT_ROOT'] . '/ByteClass/config/constants.php';
require_once $_SERVER['DOCUMENT_ROOT'] . '/ByteClass/config/database.php';
require_once $_SERVER['DOCUMENT_ROOT'] . '/ByteClass/helpers/response.php';

$db = Database::getInstance()->getConnection();

$search  = sanitize($_GET['search']  ?? '');
$action  = sanitize($_GET['action_filter'] ?? '');
$from    = sanitize($_GET['from']    ?? '');
$to      = sanitize($_GET['to']      ?? '');
$page    = max(1, (int)($_GET['page'] ?? 1));
$limit   = 30;
$offset  = ($page-1)*$limit;

$where  = ['1=1'];
$params = [];
if ($search) { $where[] = '(u.full_name LIKE ? OR al.ip_address LIKE ? OR al.description LIKE ?)'; $like="%$search%"; $params=array_merge($params,[$like,$like,$like]); }
if ($action) { $where[] = 'al.action = ?'; $params[] = $action; }
if ($from)   { $where[] = 'al.created_at >= ?'; $params[] = $from . ' 00:00:00'; }
if ($to)     { $where[] = 'al.created_at <= ?'; $params[] = $to   . ' 23:59:59'; }
$wsql = implode(' AND ', $where);

$cnt = $db->prepare("SELECT COUNT(*) FROM activity_logs al LEFT JOIN users u ON u.id=al.user_id WHERE $wsql");
$cnt->execute($params);
$total       = (int)$cnt->fetchColumn();
$total_pages = max(1, ceil($total/$limit));

$stmt = $db->prepare("
    SELECT al.*, u.full_name, u.role, u.email
    FROM activity_logs al
    LEFT JOIN users u ON u.id = al.user_id
    WHERE $wsql
    ORDER BY al.created_at DESC
    LIMIT $limit OFFSET $offset
");
$stmt->execute($params);
$logs = $stmt->fetchAll();

$actions_list = $db->query("SELECT DISTINCT action FROM activity_logs ORDER BY action")->fetchAll(PDO::FETCH_COLUMN);

$page_title = 'Activity Logs';
require_once $_SERVER['DOCUMENT_ROOT'] . '/ByteClass/includes/head.php';
?>
<div class="flex min-h-screen bg-gray-50">
  <?php require_once $_SERVER['DOCUMENT_ROOT'] . '/ByteClass/includes/sidebar-admin.php'; ?>
  <div id="main-content" class="flex-1 flex flex-col" style="margin-left:256px;">
    <?php require_once $_SERVER['DOCUMENT_ROOT'] . '/ByteClass/includes/navbar-admin.php'; ?>
    <main class="flex-1 p-6">
      <div class="bg-gradient-to-r from-indigo-600 to-cyan-500 rounded-2xl px-6 py-5 mb-6">
        <h2 class="text-white text-xl font-bold">Activity Logs</h2>
        <p class="text-indigo-100 text-sm mt-0.5">Full audit trail of all platform activity</p>
      </div>

      <form method="GET" class="bg-white rounded-2xl border border-gray-100 p-4 mb-4 flex flex-wrap gap-3">
        <div class="flex-1 min-w-48 relative">
          <i data-lucide="search" class="w-4 h-4 absolute left-3 top-1/2 -translate-y-1/2 text-gray-400"></i>
          <input type="text" name="search" value="<?= htmlspecialchars($search) ?>" placeholder="Search user, IP or description..."
            class="w-full pl-9 pr-4 py-2.5 border border-gray-200 rounded-xl text-sm focus:outline-none focus:ring-2 focus:ring-indigo-500" />
        </div>
        <select name="action_filter" class="px-4 py-2.5 border border-gray-200 rounded-xl text-sm focus:outline-none focus:ring-2 focus:ring-indigo-500">
          <option value="">All actions</option>
          <?php foreach ($actions_list as $a): ?><option value="<?= $a ?>" <?= $action===$a?'selected':'' ?>><?= htmlspecialchars($a) ?></option><?php endforeach; ?>
        </select>
        <input type="date" name="from" value="<?= $from ?>" class="px-4 py-2.5 border border-gray-200 rounded-xl text-sm focus:outline-none focus:ring-2 focus:ring-indigo-500" />
        <input type="date" name="to"   value="<?= $to ?>"   class="px-4 py-2.5 border border-gray-200 rounded-xl text-sm focus:outline-none focus:ring-2 focus:ring-indigo-500" />
        <a href="?action_filter=email_sent" class="bg-green-600 text-white px-4 py-2.5 rounded-xl text-sm font-medium hover:bg-green-700 flex items-center gap-2"><i data-lucide="mail" class="w-4 h-4"></i> Email Logs</a>
        <a href="?action_filter=email_failed" class="bg-red-100 text-red-700 px-4 py-2.5 rounded-xl text-sm font-medium hover:bg-red-200 flex items-center gap-2"><i data-lucide="mail-x" class="w-4 h-4"></i> Failed Emails</a>
        <button type="submit" class="bg-indigo-600 text-white px-4 py-2.5 rounded-xl text-sm font-medium hover:bg-indigo-700 flex items-center gap-2"><i data-lucide="filter" class="w-4 h-4"></i> Filter</button>
        <?php if ($search||$action||$from||$to): ?><a href="<?= APP_URL ?>/admin/logs.php" class="px-4 py-2.5 border border-gray-200 rounded-xl text-sm text-gray-600 hover:bg-gray-50">Clear</a><?php endif; ?>
      </form>

      <div class="bg-white rounded-2xl border border-gray-100 overflow-hidden">
        <div class="overflow-x-auto">
          <table class="w-full">
            <thead class="bg-gray-50 border-b border-gray-100">
              <tr>
                <th class="px-5 py-3 text-left text-xs font-semibold text-gray-500 uppercase">User</th>
                <th class="px-5 py-3 text-left text-xs font-semibold text-gray-500 uppercase">Action</th>
                <th class="px-5 py-3 text-left text-xs font-semibold text-gray-500 uppercase">Description</th>
                <th class="px-5 py-3 text-left text-xs font-semibold text-gray-500 uppercase">IP Address</th>
                <th class="px-5 py-3 text-left text-xs font-semibold text-gray-500 uppercase">Time</th>
              </tr>
            </thead>
            <tbody class="divide-y divide-gray-50">
              <?php if (empty($logs)): ?>
              <tr><td colspan="5" class="px-5 py-10 text-center text-gray-400 text-sm">No logs found</td></tr>
              <?php else: foreach ($logs as $l):
                $role_color = match($l['role'] ?? '') { 'admin'=>'bg-indigo-100 text-indigo-700','lecturer'=>'bg-cyan-100 text-cyan-700',default=>'bg-green-100 text-green-700' };
                $action_icons = ['login'=>'log-in','logout'=>'log-out','register'=>'user-plus','create_course'=>'book-open','create_lecturer'=>'user-plus','update_user'=>'edit','toggle_status'=>'toggle-left','unlock_account'=>'unlock','delete_user'=>'trash-2','pay_lecturer'=>'dollar-sign'];
                $icon = $action_icons[$l['action']] ?? 'activity';
              ?>
              <tr class="hover:bg-gray-50 transition-colors">
                <td class="px-5 py-3">
                  <?php if ($l['full_name']): ?>
                  <p class="text-sm font-medium text-gray-900"><?= htmlspecialchars($l['full_name']) ?></p>
                  <span class="text-xs px-2 py-0.5 rounded-full <?= $role_color ?>"><?= ucfirst($l['role'] ?? '') ?></span>
                  <?php else: ?><span class="text-sm text-gray-400">System</span><?php endif; ?>
                </td>
                <td class="px-5 py-3">
                  <div class="flex items-center gap-2">
                    <i data-lucide="<?= $icon ?>" class="w-4 h-4 text-gray-400"></i>
                    <span class="text-sm text-gray-700"><?= htmlspecialchars(str_replace('_',' ',$l['action'])) ?></span>
                  </div>
                </td>
                <td class="px-5 py-3 text-sm text-gray-600 max-w-48 truncate"><?= htmlspecialchars($l['description'] ?? '') ?></td>
                <td class="px-5 py-3 text-sm text-gray-500 font-mono text-xs"><?= htmlspecialchars($l['ip_address'] ?? '') ?></td>
                <td class="px-5 py-3 text-sm text-gray-500"><?= date('M d, Y H:i', strtotime($l['created_at'])) ?></td>
              </tr>
              <?php endforeach; endif; ?>
            </tbody>
          </table>
        </div>
        <?php if ($total_pages > 1): ?>
        <div class="px-5 py-4 border-t border-gray-100 flex items-center justify-between">
          <p class="text-sm text-gray-500">Page <?= $page ?> of <?= $total_pages ?> (<?= number_format($total) ?> records)</p>
          <div class="flex gap-2">
            <?php if ($page>1): ?><a href="?page=<?= $page-1 ?>&search=<?= urlencode($search) ?>&action_filter=<?= urlencode($action) ?>" class="px-3 py-1.5 border border-gray-200 rounded-lg text-sm hover:bg-gray-50">Previous</a><?php endif; ?>
            <?php if ($page<$total_pages): ?><a href="?page=<?= $page+1 ?>&search=<?= urlencode($search) ?>&action_filter=<?= urlencode($action) ?>" class="px-3 py-1.5 border border-gray-200 rounded-lg text-sm hover:bg-gray-50">Next</a><?php endif; ?>
          </div>
        </div>
        <?php endif; ?>
      </div>
    </main>
  </div>
</div>
<?php require_once $_SERVER['DOCUMENT_ROOT'] . '/ByteClass/includes/scripts.php'; ?>
<?php require_once $_SERVER['DOCUMENT_ROOT'] . '/ByteClass/includes/footer-minimal.php'; ?>
