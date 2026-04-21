<?php
$required_role = 'lecturer';
require_once $_SERVER['DOCUMENT_ROOT'] . '/ByteClass/includes/auth-check.php';
require_once $_SERVER['DOCUMENT_ROOT'] . '/ByteClass/config/constants.php';
require_once $_SERVER['DOCUMENT_ROOT'] . '/ByteClass/config/database.php';
require_once $_SERVER['DOCUMENT_ROOT'] . '/ByteClass/helpers/response.php';
$db = Database::getInstance()->getConnection();
$id = (int)$_SESSION['user_id'];

// Fixed: changed ORDER BY from ps.created_at to ps.id (since created_at column does not exist)
$payslips = $db->prepare("
    SELECT ps.*, u.full_name AS paid_by_name FROM payslips ps
    LEFT JOIN users u ON u.id=ps.paid_by
    WHERE ps.lecturer_id=? ORDER BY ps.id DESC
"); $payslips->execute([$id]); $payslips = $payslips->fetchAll();

$total_earned = $db->prepare("SELECT COALESCE(SUM(amount),0) FROM payslips WHERE lecturer_id=?");
$total_earned->execute([$id]); $total_earned = (float)$total_earned->fetchColumn();

$last_payment = !empty($payslips) ? $payslips[0] : null;

$page_title = 'My Payslips';
require_once $_SERVER['DOCUMENT_ROOT'] . '/ByteClass/includes/head.php';
?>
<div class="flex min-h-screen bg-gray-50">
  <?php require_once $_SERVER['DOCUMENT_ROOT'] . '/ByteClass/includes/sidebar-lecturer.php'; ?>
  <div id="main-content" class="flex-1 flex flex-col" style="margin-left:256px;">
    <?php require_once $_SERVER['DOCUMENT_ROOT'] . '/ByteClass/includes/navbar-lecturer.php'; ?>
    <main class="flex-1 p-6">
      <div class="bg-gradient-to-r from-cyan-600 to-indigo-600 rounded-2xl px-6 py-5 mb-6">
        <h2 class="text-white text-xl font-bold">My Payslips</h2>
        <p class="text-cyan-100 text-sm mt-0.5">Your payment history and records</p>
      </div>

      <!-- Summary cards -->
      <div class="grid grid-cols-1 sm:grid-cols-3 gap-4 mb-6">
        <div class="bg-white rounded-2xl border border-gray-100 p-5 flex items-center gap-4">
          <div class="w-12 h-12 bg-green-100 rounded-xl flex items-center justify-center">
            <i data-lucide="trending-up" class="w-6 h-6 text-green-600"></i>
          </div>
          <div>
            <p class="text-xl font-bold text-gray-900">KES <?= number_format($total_earned,2) ?></p>
            <p class="text-xs text-gray-500">Total earnings</p>
          </div>
        </div>
        <div class="bg-white rounded-2xl border border-gray-100 p-5 flex items-center gap-4">
          <div class="w-12 h-12 bg-indigo-100 rounded-xl flex items-center justify-center">
            <i data-lucide="file-text" class="w-6 h-6 text-indigo-600"></i>
          </div>
          <div>
            <p class="text-xl font-bold text-gray-900"><?= count($payslips) ?></p>
            <p class="text-xs text-gray-500">Total payslips</p>
          </div>
        </div>
        <div class="bg-white rounded-2xl border border-gray-100 p-5 flex items-center gap-4">
          <div class="w-12 h-12 bg-cyan-100 rounded-xl flex items-center justify-center">
            <i data-lucide="calendar" class="w-6 h-6 text-cyan-600"></i>
          </div>
          <div>
            <p class="text-xl font-bold text-gray-900"><?= $last_payment ? 'KES '.number_format($last_payment['amount'],2) : 'N/A' ?></p>
            <p class="text-xs text-gray-500">Last payment</p>
          </div>
        </div>
      </div>

      <?php if (empty($payslips)): ?>
      <div class="bg-white rounded-2xl border border-gray-100 p-12 text-center">
        <i data-lucide="file-text" class="w-10 h-10 text-gray-300 mx-auto mb-3"></i>
        <p class="text-gray-500 text-sm">No payslips yet. Payslips appear here after your administrator records a payment.</p>
      </div>
      <?php else: ?>
      <div class="bg-white rounded-2xl border border-gray-100 overflow-hidden">
        <table class="w-full">
          <thead class="bg-gray-50 border-b border-gray-100">
            <tr>
              <th class="px-5 py-3 text-left text-xs font-semibold text-gray-500 uppercase">Period</th>
              <th class="px-5 py-3 text-left text-xs font-semibold text-gray-500 uppercase">Amount</th>
              <th class="px-5 py-3 text-left text-xs font-semibold text-gray-500 uppercase">Method</th>
              <th class="px-5 py-3 text-left text-xs font-semibold text-gray-500 uppercase">Reference</th>
              <th class="px-5 py-3 text-left text-xs font-semibold text-gray-500 uppercase">Paid by</th>
              <th class="px-5 py-3 text-left text-xs font-semibold text-gray-500 uppercase">Date</th>
            </tr>
          </thead>
          <tbody class="divide-y divide-gray-50">
            <?php foreach ($payslips as $p): ?>
            <tr class="hover:bg-gray-50">
              <td class="px-5 py-4 text-sm text-gray-700">
                <?= $p['period_from'] ? date('M d', strtotime($p['period_from'])).' — '.date('M d, Y', strtotime($p['period_to'])) : 'N/A' ?>
              </td>
              <td class="px-5 py-4">
                <p class="text-sm font-bold text-gray-900"><?= $p['currency'] ?> <?= number_format($p['amount'],2) ?></p>
              </td>
              <td class="px-5 py-4 text-sm text-gray-600"><?= htmlspecialchars($p['payment_method'] ?: 'N/A') ?></td>
              <td class="px-5 py-4 text-xs font-mono text-gray-500"><?= htmlspecialchars($p['reference'] ?: '—') ?></td>
              <td class="px-5 py-4 text-sm text-gray-600"><?= htmlspecialchars($p['paid_by_name'] ?: '—') ?></td>
              <td class="px-5 py-4 text-sm text-gray-500"><?= date('M d, Y', strtotime($p['created_at'])) ?></td>
            </tr>
            <?php endforeach; ?>
          </tbody>
        </table>
      </div>
      <?php endif; ?>
    </main>
    <?php require_once $_SERVER['DOCUMENT_ROOT'] . '/ByteClass/includes/footer-minimal.php'; ?>
  </div>
</div>
<?php require_once $_SERVER['DOCUMENT_ROOT'] . '/ByteClass/includes/scripts.php'; ?>