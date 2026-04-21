<?php
$required_role = 'admin';
require_once $_SERVER['DOCUMENT_ROOT'] . '/ByteClass/includes/auth-check.php';
require_once $_SERVER['DOCUMENT_ROOT'] . '/ByteClass/config/constants.php';
require_once $_SERVER['DOCUMENT_ROOT'] . '/ByteClass/config/database.php';
require_once $_SERVER['DOCUMENT_ROOT'] . '/ByteClass/helpers/response.php';

$db = Database::getInstance()->getConnection();
$success_msg = $error_msg = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $action = $_POST['action'] ?? '';

    if ($action === 'pay_lecturer') {
        require_once $_SERVER['DOCUMENT_ROOT'] . '/ByteClass/helpers/mailer.php';
        $lid     = (int)($_POST['lecturer_id'] ?? 0);
        $amount  = (float)($_POST['amount'] ?? 0);
        $currency= sanitize($_POST['currency'] ?? 'KES');
        $method  = sanitize($_POST['payment_method'] ?? '');
        $ref     = sanitize($_POST['reference'] ?? '');
        $from    = sanitize($_POST['period_from'] ?? '');
        $to      = sanitize($_POST['period_to'] ?? '');

        if ($lid && $amount > 0 && $from && $to) {
            $db->prepare("INSERT INTO payslips (lecturer_id, amount, currency, period_from, period_to, payment_method, reference, paid_by) VALUES (?,?,?,?,?,?,?,?)")
               ->execute([$lid, $amount, $currency, $from, $to, $method, $ref, $_SESSION['user_id']]);
            $db->prepare("INSERT INTO activity_logs (user_id, action, target_type, target_id, description, ip_address) VALUES (?,?,?,?,?,?)")
               ->execute([$_SESSION['user_id'], 'pay_lecturer', 'user', $lid, "Paid lecturer KES $amount", $_SERVER['REMOTE_ADDR'] ?? '']);
            $success_msg = 'Payment recorded and payslip generated.';
        } else {
            $error_msg = 'All payment fields are required.';
        }
    }

    if ($action === 'trigger_retry') {
        $pid      = (int)($_POST['payment_id'] ?? 0);
        $deadline = date('Y-m-d H:i:s', strtotime('+48 hours'));
        $db->prepare("UPDATE payments SET retry_banner_active=1, retry_deadline=? WHERE id=?")->execute([$deadline, $pid]);
        $success_msg = 'Retry payment banner triggered for 48 hours.';
    }
}

// Filters
$gw     = sanitize($_GET['gateway'] ?? '');
$status = sanitize($_GET['status']  ?? '');
$from   = sanitize($_GET['from']    ?? '');
$to     = sanitize($_GET['to']      ?? '');
$page   = max(1, (int)($_GET['page'] ?? 1));
$limit  = 20;
$offset = ($page - 1) * $limit;

$where  = ['1=1'];
$params = [];
if ($gw)     { $where[] = 'p.gateway = ?';   $params[] = $gw; }
if ($status) { $where[] = 'p.status = ?';    $params[] = $status; }
if ($from)   { $where[] = 'p.initiated_at >= ?'; $params[] = $from . ' 00:00:00'; }
if ($to)     { $where[] = 'p.initiated_at <= ?'; $params[] = $to   . ' 23:59:59'; }
$wsql = implode(' AND ', $where);

$cnt = $db->prepare("SELECT COUNT(*) FROM payments p WHERE $wsql");
$cnt->execute($params);
$total       = (int)$cnt->fetchColumn();
$total_pages = max(1, ceil($total/$limit));

$stmt = $db->prepare("
    SELECT p.*, u.full_name, u.email, u.phone, c.name AS course_name
    FROM payments p
    JOIN users u ON u.id = p.student_id
    JOIN courses c ON c.id = p.course_id
    WHERE $wsql
    ORDER BY p.initiated_at DESC
    LIMIT $limit OFFSET $offset
");
$stmt->execute($params);
$payments = $stmt->fetchAll();

// Stats
$revenue_total  = $db->query("SELECT COALESCE(SUM(amount),0) FROM payments WHERE status='success'")->fetchColumn();
$revenue_month  = $db->query("SELECT COALESCE(SUM(amount),0) FROM payments WHERE status='success' AND MONTH(confirmed_at)=MONTH(NOW())")->fetchColumn();
$failed_count   = $db->query("SELECT COUNT(*) FROM payments WHERE status='failed'")->fetchColumn();
$pending_count  = $db->query("SELECT COUNT(*) FROM payments WHERE status='pending'")->fetchColumn();

$lecturers = $db->query("SELECT id, full_name FROM users WHERE role='lecturer' AND status='active' ORDER BY full_name")->fetchAll();

$page_title = 'Finance';
require_once $_SERVER['DOCUMENT_ROOT'] . '/ByteClass/includes/head.php';
?>
<div class="flex min-h-screen bg-gray-50">
  <?php require_once $_SERVER['DOCUMENT_ROOT'] . '/ByteClass/includes/sidebar-admin.php'; ?>
  <div id="main-content" class="flex-1 flex flex-col" style="margin-left:256px;">
    <?php require_once $_SERVER['DOCUMENT_ROOT'] . '/ByteClass/includes/navbar-admin.php'; ?>
    <main class="flex-1 p-6">
      <div class="bg-gradient-to-r from-indigo-600 to-cyan-500 rounded-2xl px-6 py-5 mb-6">
        <h2 class="text-white text-xl font-bold">Finance Overview</h2>
        <p class="text-indigo-100 text-sm mt-0.5">Track revenue, payments and lecturer payroll</p>
      </div>

      <?php if ($success_msg): ?><div class="mb-4 p-4 bg-green-50 border border-green-200 rounded-xl text-sm text-green-700 flex gap-3"><i data-lucide="check-circle" class="w-5 h-5 flex-shrink-0"></i><?= htmlspecialchars($success_msg) ?></div><?php endif; ?>
      <?php if ($error_msg): ?><div class="mb-4 p-4 bg-red-50 border border-red-200 rounded-xl text-sm text-red-700 flex gap-3"><i data-lucide="alert-circle" class="w-5 h-5 flex-shrink-0"></i><?= htmlspecialchars($error_msg) ?></div><?php endif; ?>

      <div class="grid grid-cols-2 lg:grid-cols-4 gap-4 mb-6">
        <?php foreach ([
          ['label'=>'Total revenue','value'=>'KES '.number_format($revenue_total,2),'color'=>'bg-green-500','icon'=>'trending-up'],
          ['label'=>'This month',   'value'=>'KES '.number_format($revenue_month,2),'color'=>'bg-indigo-500','icon'=>'calendar'],
          ['label'=>'Failed payments','value'=>number_format($failed_count),'color'=>'bg-red-500','icon'=>'x-circle'],
          ['label'=>'Pending',      'value'=>number_format($pending_count),'color'=>'bg-amber-500','icon'=>'clock'],
        ] as $s): ?>
        <div class="bg-white rounded-2xl p-4 border border-gray-100 flex items-center gap-3">
          <div class="w-10 h-10 <?= $s['color'] ?> rounded-xl flex items-center justify-center"><i data-lucide="<?= $s['icon'] ?>" class="w-5 h-5 text-white"></i></div>
          <div><p class="text-xl font-bold text-gray-900"><?= $s['value'] ?></p><p class="text-xs text-gray-500"><?= $s['label'] ?></p></div>
        </div>
        <?php endforeach; ?>
      </div>

      <!-- Filters -->
      <form method="GET" class="bg-white rounded-2xl border border-gray-100 p-4 mb-4 flex flex-wrap gap-3">
        <select name="gateway" class="px-4 py-2.5 border border-gray-200 rounded-xl text-sm focus:outline-none focus:ring-2 focus:ring-indigo-500">
          <option value="">All gateways</option>
          <?php foreach (['mpesa','stripe','paypal','flutterwave','paystack'] as $g): ?>
          <option value="<?= $g ?>" <?= $gw===$g?'selected':'' ?>><?= strtoupper($g) ?></option>
          <?php endforeach; ?>
        </select>
        <select name="status" class="px-4 py-2.5 border border-gray-200 rounded-xl text-sm focus:outline-none focus:ring-2 focus:ring-indigo-500">
          <option value="">All statuses</option>
          <?php foreach (['success','failed','pending','refunded'] as $st): ?>
          <option value="<?= $st ?>" <?= $status===$st?'selected':'' ?>><?= ucfirst($st) ?></option>
          <?php endforeach; ?>
        </select>
        <input type="date" name="from" value="<?= $from ?>" class="px-4 py-2.5 border border-gray-200 rounded-xl text-sm focus:outline-none focus:ring-2 focus:ring-indigo-500" />
        <input type="date" name="to"   value="<?= $to ?>"   class="px-4 py-2.5 border border-gray-200 rounded-xl text-sm focus:outline-none focus:ring-2 focus:ring-indigo-500" />
        <button type="submit" class="bg-indigo-600 text-white px-4 py-2.5 rounded-xl text-sm font-medium hover:bg-indigo-700 flex items-center gap-2"><i data-lucide="filter" class="w-4 h-4"></i> Filter</button>
        <?php if ($gw||$status||$from||$to): ?><a href="<?= APP_URL ?>/admin/finance.php" class="px-4 py-2.5 border border-gray-200 rounded-xl text-sm text-gray-600 hover:bg-gray-50">Clear</a><?php endif; ?>
      </form>

      <div class="bg-white rounded-2xl border border-gray-100 overflow-hidden">
        <div class="overflow-x-auto">
          <table class="w-full">
            <thead class="bg-gray-50 border-b border-gray-100">
              <tr>
                <th class="px-5 py-3 text-left text-xs font-semibold text-gray-500 uppercase">Student</th>
                <th class="px-5 py-3 text-left text-xs font-semibold text-gray-500 uppercase">Course</th>
                <th class="px-5 py-3 text-left text-xs font-semibold text-gray-500 uppercase">Amount</th>
                <th class="px-5 py-3 text-left text-xs font-semibold text-gray-500 uppercase">Gateway</th>
                <th class="px-5 py-3 text-left text-xs font-semibold text-gray-500 uppercase">Status</th>
                <th class="px-5 py-3 text-left text-xs font-semibold text-gray-500 uppercase">Date</th>
                <th class="px-5 py-3 text-left text-xs font-semibold text-gray-500 uppercase">Actions</th>
              </tr>
            </thead>
            <tbody class="divide-y divide-gray-50">
              <?php if (empty($payments)): ?>
              <tr><td colspan="7" class="px-5 py-10 text-center text-gray-400 text-sm">No payments found</td></tr>
              <?php else: foreach ($payments as $p):
                $sc = match($p['status']) { 'success'=>'bg-green-100 text-green-700','failed'=>'bg-red-100 text-red-700','pending'=>'bg-amber-100 text-amber-700',default=>'bg-gray-100 text-gray-600'};
              ?>
              <tr class="hover:bg-gray-50 transition-colors">
                <td class="px-5 py-4">
                  <p class="text-sm font-medium text-gray-900"><?= htmlspecialchars($p['full_name']) ?></p>
                  <p class="text-xs text-gray-400"><?= htmlspecialchars($p['phone']) ?></p>
                </td>
                <td class="px-5 py-4 text-sm text-gray-600 max-w-32 truncate"><?= htmlspecialchars($p['course_name']) ?></td>
                <td class="px-5 py-4">
                  <p class="text-sm font-bold text-gray-900"><?= $p['currency'] ?> <?= number_format($p['amount'],2) ?></p>
                  <p class="text-xs text-gray-400"><?= htmlspecialchars($p['receipt_id']) ?></p>
                </td>
                <td class="px-5 py-4"><span class="text-xs font-semibold bg-gray-100 text-gray-700 px-2.5 py-1 rounded-full uppercase"><?= $p['gateway'] ?></span></td>
                <td class="px-5 py-4"><span class="px-2.5 py-1 rounded-full text-xs font-medium <?= $sc ?>"><?= ucfirst($p['status']) ?></span></td>
                <td class="px-5 py-4 text-sm text-gray-500"><?= date('M d, Y H:i', strtotime($p['initiated_at'])) ?></td>
                <td class="px-5 py-4">
                  <?php if ($p['status'] === 'failed'): ?>
                  <form method="POST" class="inline">
                    <input type="hidden" name="action" value="trigger_retry">
                    <input type="hidden" name="payment_id" value="<?= $p['id'] ?>">
                    <button type="submit" class="text-xs bg-amber-100 text-amber-700 px-3 py-1.5 rounded-lg hover:bg-amber-200 font-medium">Retry Banner</button>
                  </form>
                  <?php endif; ?>
                </td>
              </tr>
              <?php endforeach; endif; ?>
            </tbody>
          </table>
        </div>
        <?php if ($total_pages > 1): ?>
        <div class="px-5 py-4 border-t border-gray-100 flex items-center justify-between">
          <p class="text-sm text-gray-500">Page <?= $page ?> of <?= $total_pages ?> (<?= number_format($total) ?> records)</p>
          <div class="flex gap-2">
            <?php if ($page>1): ?><a href="?page=<?= $page-1 ?>&gateway=<?= urlencode($gw) ?>&status=<?= urlencode($status) ?>" class="px-3 py-1.5 border border-gray-200 rounded-lg text-sm hover:bg-gray-50">Previous</a><?php endif; ?>
            <?php if ($page<$total_pages): ?><a href="?page=<?= $page+1 ?>&gateway=<?= urlencode($gw) ?>&status=<?= urlencode($status) ?>" class="px-3 py-1.5 border border-gray-200 rounded-lg text-sm hover:bg-gray-50">Next</a><?php endif; ?>
          </div>
        </div>
        <?php endif; ?>
      </div>
    </main>
  </div>
</div>

<!-- PAY LECTURER MODAL -->
<div id="pay-modal" class="hidden fixed inset-0 z-50 flex items-center justify-center p-4" style="background:rgba(0,0,0,0.5);">
  <div class="bg-white rounded-2xl w-full max-w-md shadow-xl">
    <div class="flex items-center justify-between px-6 py-4 border-b border-gray-100">
      <h3 class="font-semibold text-gray-900 flex items-center gap-2"><i data-lucide="dollar-sign" class="w-5 h-5 text-indigo-600"></i> Pay Lecturer</h3>
      <button onclick="document.getElementById('pay-modal').classList.add('hidden')" class="text-gray-400 hover:text-gray-600"><i data-lucide="x" class="w-5 h-5"></i></button>
    </div>
    <form method="POST" class="p-6 space-y-4">
      <input type="hidden" name="action" value="pay_lecturer">
      <div>
        <label class="block text-sm font-medium text-gray-700 mb-1.5">Lecturer *</label>
        <select name="lecturer_id" required class="w-full px-4 py-3 border border-gray-200 rounded-xl text-sm focus:outline-none focus:ring-2 focus:ring-indigo-500">
          <option value="">Select lecturer</option>
          <?php foreach ($lecturers as $l): ?><option value="<?= $l['id'] ?>"><?= htmlspecialchars($l['full_name']) ?></option><?php endforeach; ?>
        </select>
      </div>
      <div class="grid grid-cols-2 gap-4">
        <div>
          <label class="block text-sm font-medium text-gray-700 mb-1.5">Amount *</label>
          <input type="number" name="amount" required step="0.01" min="1" placeholder="0.00" class="w-full px-4 py-3 border border-gray-200 rounded-xl text-sm focus:outline-none focus:ring-2 focus:ring-indigo-500" />
        </div>
        <div>
          <label class="block text-sm font-medium text-gray-700 mb-1.5">Currency</label>
          <select name="currency" class="w-full px-4 py-3 border border-gray-200 rounded-xl text-sm focus:outline-none focus:ring-2 focus:ring-indigo-500">
            <option value="KES">KES</option>
            <option value="USD">USD</option>
          </select>
        </div>
        <div>
          <label class="block text-sm font-medium text-gray-700 mb-1.5">Period from *</label>
          <input type="date" name="period_from" required class="w-full px-4 py-3 border border-gray-200 rounded-xl text-sm focus:outline-none focus:ring-2 focus:ring-indigo-500" />
        </div>
        <div>
          <label class="block text-sm font-medium text-gray-700 mb-1.5">Period to *</label>
          <input type="date" name="period_to" required class="w-full px-4 py-3 border border-gray-200 rounded-xl text-sm focus:outline-none focus:ring-2 focus:ring-indigo-500" />
        </div>
        <div>
          <label class="block text-sm font-medium text-gray-700 mb-1.5">Payment method</label>
          <input type="text" name="payment_method" placeholder="e.g. M-Pesa" class="w-full px-4 py-3 border border-gray-200 rounded-xl text-sm focus:outline-none focus:ring-2 focus:ring-indigo-500" />
        </div>
        <div>
          <label class="block text-sm font-medium text-gray-700 mb-1.5">Reference</label>
          <input type="text" name="reference" placeholder="Transaction ref" class="w-full px-4 py-3 border border-gray-200 rounded-xl text-sm focus:outline-none focus:ring-2 focus:ring-indigo-500" />
        </div>
      </div>
      <div class="flex gap-3 pt-2">
        <button type="button" onclick="document.getElementById('pay-modal').classList.add('hidden')" class="flex-1 px-4 py-3 border border-gray-200 rounded-xl text-sm font-medium text-gray-700 hover:bg-gray-50">Cancel</button>
        <button type="submit" class="flex-1 bg-indigo-600 hover:bg-indigo-700 text-white px-4 py-3 rounded-xl text-sm font-medium flex items-center justify-center gap-2"><i data-lucide="send" class="w-4 h-4"></i> Record Payment</button>
      </div>
    </form>
  </div>
</div>

<?php require_once $_SERVER['DOCUMENT_ROOT'] . '/ByteClass/includes/scripts.php'; ?>
<?php require_once $_SERVER['DOCUMENT_ROOT'] . '/ByteClass/includes/footer-minimal.php'; ?>