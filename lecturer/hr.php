<?php
$required_role = 'lecturer';
require_once $_SERVER['DOCUMENT_ROOT'] . '/ByteClass/includes/auth-check.php';
require_once $_SERVER['DOCUMENT_ROOT'] . '/ByteClass/config/constants.php';
require_once $_SERVER['DOCUMENT_ROOT'] . '/ByteClass/config/database.php';
require_once $_SERVER['DOCUMENT_ROOT'] . '/ByteClass/helpers/response.php';
$db = Database::getInstance()->getConnection();
$id = (int)$_SESSION['user_id'];
$success_msg = $error_msg = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $action = $_POST['action'] ?? '';
    if ($action === 'apply_leave') {
        $type   = sanitize($_POST['type']       ?? 'annual');
        $start  = sanitize($_POST['start_date'] ?? '');
        $end    = sanitize($_POST['end_date']   ?? '');
        $reason = sanitize($_POST['reason']     ?? '');
        if (!$start||!$end||!$reason) { $error_msg='All fields required.'; }
        else {
            $db->prepare("INSERT INTO leave_requests (lecturer_id, type, start_date, end_date, reason) VALUES (?,?,?,?,?)")
               ->execute([$id,$type,$start,$end,$reason]);
            foreach ($db->query("SELECT id FROM users WHERE role='admin'")->fetchAll() as $a) {
                $db->prepare("INSERT INTO notifications (user_id,title,message,type) VALUES (?,?,?,?)")
                   ->execute([$a['id'],'New Leave Request',$_SESSION['full_name'].' submitted a leave request.','hr']);
            }
            $success_msg = 'Leave request submitted.';
        }
    }
    if ($action === 'submit_hr') {
        $form_type = sanitize($_POST['form_type']   ?? 'complaint');
        $subject   = sanitize($_POST['subject']     ?? '');
        $desc      = sanitize($_POST['description'] ?? '');
        if (!$subject||!$desc) { $error_msg='All fields required.'; }
        else {
            $db->prepare("INSERT INTO hr_forms (lecturer_id, form_type, subject, description) VALUES (?,?,?,?)")
               ->execute([$id,$form_type,$subject,$desc]);
            $success_msg = 'HR form submitted.';
        }
    }
}

$leave_requests = $db->prepare("SELECT * FROM leave_requests WHERE lecturer_id=? ORDER BY created_at DESC LIMIT 10");
$leave_requests->execute([$id]); $leave_requests = $leave_requests->fetchAll();

$hr_forms = $db->prepare("SELECT * FROM hr_forms WHERE lecturer_id=? ORDER BY created_at DESC LIMIT 10");
$hr_forms->execute([$id]); $hr_forms = $hr_forms->fetchAll();

// Contract info
$contract = $db->prepare("SELECT contract_start, contract_end, contract_pdf FROM lecturer_profiles WHERE user_id=?");
$contract->execute([$id]); $contract = $contract->fetch();

$tab = sanitize($_GET['tab'] ?? 'leave');
$page_title = 'HR & Forms';
require_once $_SERVER['DOCUMENT_ROOT'] . '/ByteClass/includes/head.php';
?>
<div class="flex min-h-screen bg-gray-50">
  <?php require_once $_SERVER['DOCUMENT_ROOT'] . '/ByteClass/includes/sidebar-lecturer.php'; ?>
  <div id="main-content" class="flex-1 flex flex-col" style="margin-left:256px;">
    <?php require_once $_SERVER['DOCUMENT_ROOT'] . '/ByteClass/includes/navbar-lecturer.php'; ?>
    <main class="flex-1 p-6">
      <div class="bg-gradient-to-r from-cyan-600 to-indigo-600 rounded-2xl px-6 py-5 mb-6">
        <h2 class="text-white text-xl font-bold">HR & Forms</h2>
        <p class="text-cyan-100 text-sm mt-0.5">Leave requests, HR forms and your contract</p>
      </div>

      <?php if ($success_msg): ?><div class="mb-4 p-4 bg-green-50 border border-green-200 rounded-xl text-sm text-green-700 flex gap-3"><i data-lucide="check-circle" class="w-5 h-5 flex-shrink-0"></i><?= htmlspecialchars($success_msg) ?></div><?php endif; ?>
      <?php if ($error_msg): ?><div class="mb-4 p-4 bg-red-50 border border-red-200 rounded-xl text-sm text-red-700 flex gap-3"><i data-lucide="alert-circle" class="w-5 h-5 flex-shrink-0"></i><?= htmlspecialchars($error_msg) ?></div><?php endif; ?>

      <!-- Contract banner -->
      <?php if ($contract && ($contract['contract_start'] || $contract['contract_pdf'])): ?>
      <div class="bg-indigo-50 border border-indigo-200 rounded-2xl p-4 mb-6 flex items-center justify-between gap-4">
        <div class="flex items-center gap-3">
          <i data-lucide="file-text" class="w-5 h-5 text-indigo-600"></i>
          <div>
            <p class="text-sm font-semibold text-indigo-800">Employment Contract</p>
            <?php if ($contract['contract_start'] && $contract['contract_end']): ?>
            <p class="text-xs text-indigo-600">
              <?= date('M d, Y', strtotime($contract['contract_start'])) ?> — <?= date('M d, Y', strtotime($contract['contract_end'])) ?>
              <?php
              $days_left = (int)((strtotime($contract['contract_end'])-time())/86400);
              if ($days_left < 30 && $days_left > 0): ?>
              <span class="text-amber-600 font-semibold ml-2">⚠ Expiring in <?= $days_left ?> days</span>
              <?php elseif ($days_left <= 0): ?><span class="text-red-600 font-semibold ml-2">Expired</span>
              <?php endif; ?>
            </p>
            <?php endif; ?>
          </div>
        </div>
        <?php if ($contract['contract_pdf']): ?>
        <a href="<?= htmlspecialchars($contract['contract_pdf']) ?>" target="_blank"
           class="bg-indigo-600 text-white px-4 py-2 rounded-xl text-xs font-semibold hover:bg-indigo-700 flex items-center gap-1.5">
          <i data-lucide="download" class="w-3.5 h-3.5"></i> Download
        </a>
        <?php endif; ?>
      </div>
      <?php endif; ?>

      <!-- Tabs -->
      <div class="flex gap-2 mb-6 border-b border-gray-200">
        <a href="?tab=leave" class="px-4 py-3 text-sm font-medium border-b-2 transition-colors <?= $tab==='leave' ? 'border-indigo-600 text-indigo-600' : 'border-transparent text-gray-500 hover:text-gray-700' ?>">Leave Requests</a>
        <a href="?tab=hr"    class="px-4 py-3 text-sm font-medium border-b-2 transition-colors <?= $tab==='hr'    ? 'border-indigo-600 text-indigo-600' : 'border-transparent text-gray-500 hover:text-gray-700' ?>">HR Forms</a>
      </div>

      <?php if ($tab === 'leave'): ?>
      <div class="grid grid-cols-1 lg:grid-cols-2 gap-6">
        <!-- Submit leave form -->
        <div class="bg-white rounded-2xl border border-gray-100 p-5">
          <h3 class="font-semibold text-gray-900 mb-4">Apply for Leave</h3>
          <form method="POST" class="space-y-4">
            <input type="hidden" name="action" value="apply_leave">
            <div>
              <label class="block text-sm font-medium text-gray-700 mb-1.5">Leave type</label>
              <select name="type" class="w-full px-4 py-3 border border-gray-200 rounded-xl text-sm focus:outline-none focus:ring-2 focus:ring-indigo-500">
                <option value="annual">Annual Leave</option>
                <option value="sick">Sick Leave</option>
                <option value="emergency">Emergency Leave</option>
                <option value="maternity">Maternity/Paternity Leave</option>
                <option value="study">Study Leave</option>
                <option value="other">Other</option>
              </select>
            </div>
            <div class="grid grid-cols-2 gap-4">
              <div>
                <label class="block text-sm font-medium text-gray-700 mb-1.5">Start date *</label>
                <input type="date" name="start_date" required class="w-full px-4 py-3 border border-gray-200 rounded-xl text-sm focus:outline-none focus:ring-2 focus:ring-indigo-500" />
              </div>
              <div>
                <label class="block text-sm font-medium text-gray-700 mb-1.5">End date *</label>
                <input type="date" name="end_date" required class="w-full px-4 py-3 border border-gray-200 rounded-xl text-sm focus:outline-none focus:ring-2 focus:ring-indigo-500" />
              </div>
            </div>
            <div>
              <label class="block text-sm font-medium text-gray-700 mb-1.5">Reason *</label>
              <textarea name="reason" required rows="3" class="w-full px-4 py-3 border border-gray-200 rounded-xl text-sm focus:outline-none focus:ring-2 focus:ring-indigo-500 resize-none"></textarea>
            </div>
            <button type="submit" class="w-full bg-indigo-600 text-white py-3 rounded-xl text-sm font-semibold hover:bg-indigo-700 flex items-center justify-center gap-2">
              <i data-lucide="send" class="w-4 h-4"></i> Submit Request
            </button>
          </form>
        </div>
        <!-- Leave history -->
        <div>
          <h3 class="font-semibold text-gray-900 mb-4">My Leave History</h3>
          <?php if (empty($leave_requests)): ?>
          <div class="bg-white rounded-2xl border border-gray-100 p-8 text-center text-gray-400 text-sm">No leave requests yet</div>
          <?php else: foreach ($leave_requests as $lr):
            $sc = match($lr['status']) { 'approved'=>'bg-green-100 text-green-700','rejected'=>'bg-red-100 text-red-700',default=>'bg-amber-100 text-amber-700'};
          ?>
          <div class="bg-white rounded-xl border border-gray-100 p-4 mb-3">
            <div class="flex items-start justify-between gap-2">
              <div>
                <p class="font-semibold text-gray-900 text-sm"><?= ucfirst($lr['type']) ?> Leave</p>
                <p class="text-xs text-gray-400 mt-0.5"><?= date('M d', strtotime($lr['start_date'])) ?> → <?= date('M d, Y', strtotime($lr['end_date'])) ?></p>
                <p class="text-xs text-gray-600 mt-1"><?= htmlspecialchars(substr($lr['reason'],0,60)) ?></p>
                <?php if ($lr['decision_note']): ?>
                <p class="text-xs text-gray-500 mt-1 italic">Note: <?= htmlspecialchars($lr['decision_note']) ?></p>
                <?php endif; ?>
              </div>
              <span class="text-xs px-2.5 py-1 rounded-full font-medium <?= $sc ?> flex-shrink-0"><?= ucfirst($lr['status']) ?></span>
            </div>
          </div>
          <?php endforeach; endif; ?>
        </div>
      </div>

      <?php else: ?>
      <div class="grid grid-cols-1 lg:grid-cols-2 gap-6">
        <div class="bg-white rounded-2xl border border-gray-100 p-5">
          <h3 class="font-semibold text-gray-900 mb-4">Submit HR Form</h3>
          <form method="POST" class="space-y-4">
            <input type="hidden" name="action" value="submit_hr">
            <div>
              <label class="block text-sm font-medium text-gray-700 mb-1.5">Form type</label>
              <select name="form_type" class="w-full px-4 py-3 border border-gray-200 rounded-xl text-sm focus:outline-none focus:ring-2 focus:ring-indigo-500">
                <option value="complaint">Complaint</option>
                <option value="request">General Request</option>
                <option value="feedback">Feedback</option>
                <option value="clearance">Clearance Request</option>
                <option value="other">Other</option>
              </select>
            </div>
            <div>
              <label class="block text-sm font-medium text-gray-700 mb-1.5">Subject *</label>
              <input type="text" name="subject" required class="w-full px-4 py-3 border border-gray-200 rounded-xl text-sm focus:outline-none focus:ring-2 focus:ring-indigo-500" />
            </div>
            <div>
              <label class="block text-sm font-medium text-gray-700 mb-1.5">Description *</label>
              <textarea name="description" required rows="4" class="w-full px-4 py-3 border border-gray-200 rounded-xl text-sm focus:outline-none focus:ring-2 focus:ring-indigo-500 resize-none"></textarea>
            </div>
            <button type="submit" class="w-full bg-indigo-600 text-white py-3 rounded-xl text-sm font-semibold hover:bg-indigo-700 flex items-center justify-center gap-2">
              <i data-lucide="send" class="w-4 h-4"></i> Submit Form
            </button>
          </form>
        </div>
        <div>
          <h3 class="font-semibold text-gray-900 mb-4">My Submissions</h3>
          <?php if (empty($hr_forms)): ?>
          <div class="bg-white rounded-2xl border border-gray-100 p-8 text-center text-gray-400 text-sm">No HR forms submitted</div>
          <?php else: foreach ($hr_forms as $hf):
            $sc = match($hf['status']) { 'resolved'=>'bg-green-100 text-green-700','rejected'=>'bg-red-100 text-red-700','in_review'=>'bg-blue-100 text-blue-700',default=>'bg-amber-100 text-amber-700'};
          ?>
          <div class="bg-white rounded-xl border border-gray-100 p-4 mb-3">
            <div class="flex items-start justify-between gap-2">
              <div>
                <p class="font-semibold text-gray-900 text-sm"><?= htmlspecialchars($hf['subject']) ?></p>
                <p class="text-xs text-gray-400 mt-0.5"><?= ucwords(str_replace('_',' ',$hf['form_type'])) ?> · <?= date('M d, Y', strtotime($hf['created_at'])) ?></p>
                <?php if ($hf['admin_note']): ?>
                <p class="text-xs text-gray-500 mt-1 italic">Admin: <?= htmlspecialchars($hf['admin_note']) ?></p>
                <?php endif; ?>
              </div>
              <span class="text-xs px-2.5 py-1 rounded-full font-medium <?= $sc ?> flex-shrink-0"><?= ucfirst($hf['status']) ?></span>
            </div>
          </div>
          <?php endforeach; endif; ?>
        </div>
      </div>
      <?php endif; ?>
    </main>
    <?php require_once $_SERVER['DOCUMENT_ROOT'] . '/ByteClass/includes/footer-minimal.php'; ?>
  </div>
</div>
<?php require_once $_SERVER['DOCUMENT_ROOT'] . '/ByteClass/includes/scripts.php'; ?>
