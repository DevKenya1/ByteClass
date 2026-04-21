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

    if ($action === 'decide_leave') {
        $id     = (int)($_POST['leave_id'] ?? 0);
        $status = sanitize($_POST['status'] ?? '');
        $note   = sanitize($_POST['note'] ?? '');
        if (in_array($status, ['approved','rejected'])) {
            $db->prepare("UPDATE leave_requests SET status=?, approved_by=?, decision_note=?, decided_at=NOW() WHERE id=?")
               ->execute([$status, $_SESSION['user_id'], $note, $id]);
            // Notify lecturer
            $lr = $db->prepare("SELECT lecturer_id FROM leave_requests WHERE id=?");
            $lr->execute([$id]);
            $lid = $lr->fetchColumn();
            $db->prepare("INSERT INTO notifications (user_id, title, message, type) VALUES (?,?,?,?)")
               ->execute([$lid, 'Leave Request ' . ucfirst($status), "Your leave request has been $status." . ($note ? " Note: $note" : ''), 'hr']);
            $success_msg = "Leave request $status.";
        }
    }

    if ($action === 'upload_contract') {
        $lid = (int)($_POST['lecturer_id'] ?? 0);
        $start = sanitize($_POST['contract_start'] ?? '');
        $end   = sanitize($_POST['contract_end'] ?? '');
        $contract_path = null;

        if (isset($_FILES['contract']) && $_FILES['contract']['error'] === 0) {
            $fname = 'contract_' . $lid . '_' . time() . '.pdf';
            $dest  = $_SERVER['DOCUMENT_ROOT'] . '/ByteClass/uploads/contracts/' . $fname;
            if (move_uploaded_file($_FILES['contract']['tmp_name'], $dest)) {
                $contract_path = APP_URL . '/uploads/contracts/' . $fname;
            }
        }

        $db->prepare("UPDATE lecturer_profiles SET contract_start=?, contract_end=?" . ($contract_path ? ", contract_pdf=?" : "") . " WHERE user_id=?")
           ->execute($contract_path ? [$start, $end, $contract_path, $lid] : [$start, $end, $lid]);

        $success_msg = 'Contract updated successfully.';
    }

    if ($action === 'decide_hr') {
        $id     = (int)($_POST['form_id'] ?? 0);
        $status = sanitize($_POST['status'] ?? '');
        $note   = sanitize($_POST['note'] ?? '');
        if (in_array($status, ['in_review','resolved','rejected'])) {
            $db->prepare("UPDATE hr_forms SET status=?, admin_note=?, handled_by=?, updated_at=NOW() WHERE id=?")
               ->execute([$status, $note, $_SESSION['user_id'], $id]);
            $success_msg = "HR form updated.";
        }
    }
}

$tab = sanitize($_GET['tab'] ?? 'leave');

$leave_requests = $db->query("
    SELECT lr.*, u.full_name, u.email, ab.full_name AS approved_by_name
    FROM leave_requests lr
    JOIN users u ON u.id = lr.lecturer_id
    LEFT JOIN users ab ON ab.id = lr.approved_by
    ORDER BY lr.created_at DESC LIMIT 50
")->fetchAll();

$hr_forms = $db->query("
    SELECT hf.*, u.full_name, hb.full_name AS handled_by_name
    FROM hr_forms hf
    JOIN users u ON u.id = hf.lecturer_id
    LEFT JOIN users hb ON hb.id = hf.handled_by
    ORDER BY hf.created_at DESC LIMIT 50
")->fetchAll();

$lecturers = $db->query("SELECT u.id, u.full_name, lp.contract_start, lp.contract_end, lp.contract_pdf FROM users u JOIN lecturer_profiles lp ON lp.user_id=u.id WHERE u.role='lecturer' ORDER BY u.full_name")->fetchAll();

$pending_leave = $db->query("SELECT COUNT(*) FROM leave_requests WHERE status='pending'")->fetchColumn();
$pending_hr    = $db->query("SELECT COUNT(*) FROM hr_forms WHERE status='pending'")->fetchColumn();

$page_title = 'HR & Approvals';
require_once $_SERVER['DOCUMENT_ROOT'] . '/ByteClass/includes/head.php';
?>
<div class="flex min-h-screen bg-gray-50">
  <?php require_once $_SERVER['DOCUMENT_ROOT'] . '/ByteClass/includes/sidebar-admin.php'; ?>
  <div id="main-content" class="flex-1 flex flex-col" style="margin-left:256px;">
    <?php require_once $_SERVER['DOCUMENT_ROOT'] . '/ByteClass/includes/navbar-admin.php'; ?>
    <main class="flex-1 p-6">
      <div class="bg-gradient-to-r from-indigo-600 to-cyan-500 rounded-2xl px-6 py-5 mb-6">
        <h2 class="text-white text-xl font-bold">HR & Approvals</h2>
        <p class="text-indigo-100 text-sm mt-0.5">Manage leave requests, contracts and HR forms</p>
      </div>

      <?php if ($success_msg): ?><div class="mb-4 p-4 bg-green-50 border border-green-200 rounded-xl text-sm text-green-700 flex gap-3"><i data-lucide="check-circle" class="w-5 h-5 flex-shrink-0"></i><?= htmlspecialchars($success_msg) ?></div><?php endif; ?>
      <?php if ($error_msg): ?><div class="mb-4 p-4 bg-red-50 border border-red-200 rounded-xl text-sm text-red-700 flex gap-3"><i data-lucide="alert-circle" class="w-5 h-5 flex-shrink-0"></i><?= htmlspecialchars($error_msg) ?></div><?php endif; ?>

      <!-- Tabs -->
      <div class="flex gap-2 mb-6 border-b border-gray-200">
        <?php foreach ([
          ['tab'=>'leave',     'label'=>'Leave Requests',  'count'=>$pending_leave],
          ['tab'=>'hr',        'label'=>'HR Forms',        'count'=>$pending_hr],
          ['tab'=>'contracts', 'label'=>'Contracts',       'count'=>0],
        ] as $t): ?>
        <a href="?tab=<?= $t['tab'] ?>"
           class="px-4 py-3 text-sm font-medium flex items-center gap-2 border-b-2 transition-colors <?= $tab===$t['tab'] ? 'border-indigo-600 text-indigo-600' : 'border-transparent text-gray-500 hover:text-gray-700' ?>">
          <?= $t['label'] ?>
          <?php if ($t['count'] > 0): ?>
          <span class="bg-red-500 text-white text-xs px-1.5 py-0.5 rounded-full"><?= $t['count'] ?></span>
          <?php endif; ?>
        </a>
        <?php endforeach; ?>
      </div>

      <?php if ($tab === 'leave'): ?>
      <div class="space-y-3">
        <?php if (empty($leave_requests)): ?>
        <div class="bg-white rounded-2xl border border-gray-100 p-10 text-center text-gray-400">No leave requests</div>
        <?php else: foreach ($leave_requests as $lr):
          $sc = match($lr['status']) { 'approved'=>'bg-green-100 text-green-700','rejected'=>'bg-red-100 text-red-700',default=>'bg-amber-100 text-amber-700'};
        ?>
        <div class="bg-white rounded-2xl border border-gray-100 p-5">
          <div class="flex items-start justify-between gap-4">
            <div class="flex-1">
              <div class="flex items-center gap-2 mb-2">
                <p class="font-semibold text-gray-900"><?= htmlspecialchars($lr['full_name']) ?></p>
                <span class="text-xs px-2 py-0.5 rounded-full font-medium <?= $sc ?>"><?= ucfirst($lr['status']) ?></span>
                <span class="text-xs bg-gray-100 text-gray-600 px-2 py-0.5 rounded-full"><?= ucfirst($lr['type']) ?></span>
              </div>
              <p class="text-sm text-gray-600 mb-2"><?= htmlspecialchars($lr['reason']) ?></p>
              <div class="flex items-center gap-4 text-xs text-gray-400">
                <span><?= date('M d', strtotime($lr['start_date'])) ?> → <?= date('M d, Y', strtotime($lr['end_date'])) ?></span>
                <span>Submitted <?= date('M d, Y', strtotime($lr['created_at'])) ?></span>
                <?php if ($lr['approved_by_name']): ?><span>Decided by <?= htmlspecialchars($lr['approved_by_name']) ?></span><?php endif; ?>
              </div>
              <?php if ($lr['decision_note']): ?><p class="text-xs text-gray-500 mt-1 italic">Note: <?= htmlspecialchars($lr['decision_note']) ?></p><?php endif; ?>
            </div>
            <?php if ($lr['status'] === 'pending'): ?>
            <div class="flex gap-2 flex-shrink-0">
              <button onclick="decideLeave(<?= $lr['id'] ?>, 'approved')"
                class="px-3 py-1.5 bg-green-100 text-green-700 rounded-lg text-xs font-medium hover:bg-green-200">Approve</button>
              <button onclick="decideLeave(<?= $lr['id'] ?>, 'rejected')"
                class="px-3 py-1.5 bg-red-100 text-red-700 rounded-lg text-xs font-medium hover:bg-red-200">Reject</button>
            </div>
            <?php endif; ?>
          </div>
        </div>
        <?php endforeach; endif; ?>
      </div>

      <?php elseif ($tab === 'hr'): ?>
      <div class="space-y-3">
        <?php if (empty($hr_forms)): ?>
        <div class="bg-white rounded-2xl border border-gray-100 p-10 text-center text-gray-400">No HR forms submitted</div>
        <?php else: foreach ($hr_forms as $hf):
          $sc = match($hf['status']) { 'resolved'=>'bg-green-100 text-green-700','rejected'=>'bg-red-100 text-red-700','in_review'=>'bg-blue-100 text-blue-700',default=>'bg-amber-100 text-amber-700'};
        ?>
        <div class="bg-white rounded-2xl border border-gray-100 p-5">
          <div class="flex items-start justify-between gap-4">
            <div class="flex-1">
              <div class="flex items-center gap-2 mb-2">
                <p class="font-semibold text-gray-900"><?= htmlspecialchars($hf['full_name']) ?></p>
                <span class="text-xs px-2 py-0.5 rounded-full font-medium <?= $sc ?>"><?= ucfirst($hf['status']) ?></span>
                <span class="text-xs bg-gray-100 text-gray-600 px-2 py-0.5 rounded-full"><?= ucwords(str_replace('_',' ',$hf['form_type'])) ?></span>
              </div>
              <p class="text-sm font-medium text-gray-800 mb-1"><?= htmlspecialchars($hf['subject']) ?></p>
              <p class="text-sm text-gray-600"><?= htmlspecialchars($hf['description']) ?></p>
              <?php if ($hf['admin_note']): ?><p class="text-xs text-gray-500 mt-1 italic">Admin note: <?= htmlspecialchars($hf['admin_note']) ?></p><?php endif; ?>
            </div>
            <?php if ($hf['status'] === 'pending' || $hf['status'] === 'in_review'): ?>
            <button onclick="decideHR(<?= $hf['id'] ?>)" class="px-3 py-1.5 bg-indigo-100 text-indigo-700 rounded-lg text-xs font-medium hover:bg-indigo-200 flex-shrink-0">Respond</button>
            <?php endif; ?>
          </div>
        </div>
        <?php endforeach; endif; ?>
      </div>

      <?php elseif ($tab === 'contracts'): ?>
      <div class="space-y-3">
        <?php foreach ($lecturers as $l):
          $expiring = $l['contract_end'] && strtotime($l['contract_end']) < strtotime('+30 days') && strtotime($l['contract_end']) > time();
          $expired  = $l['contract_end'] && strtotime($l['contract_end']) < time();
        ?>
        <div class="bg-white rounded-2xl border <?= $expiring ? 'border-amber-200' : ($expired ? 'border-red-200' : 'border-gray-100') ?> p-5">
          <div class="flex items-center justify-between gap-4">
            <div>
              <p class="font-semibold text-gray-900"><?= htmlspecialchars($l['full_name']) ?></p>
              <div class="flex items-center gap-3 mt-1 text-xs text-gray-500">
                <?php if ($l['contract_start']): ?><span>Start: <?= date('M d, Y', strtotime($l['contract_start'])) ?></span><?php endif; ?>
                <?php if ($l['contract_end']): ?>
                <span class="<?= $expired ? 'text-red-500 font-semibold' : ($expiring ? 'text-amber-600 font-semibold' : '') ?>">
                  End: <?= date('M d, Y', strtotime($l['contract_end'])) ?>
                  <?php if ($expiring): ?>(Expiring soon)<?php elseif ($expired): ?>(Expired)<?php endif; ?>
                </span>
                <?php endif; ?>
              </div>
            </div>
            <div class="flex items-center gap-3">
              <?php if ($l['contract_pdf']): ?>
              <a href="<?= htmlspecialchars($l['contract_pdf']) ?>" target="_blank"
                class="px-3 py-1.5 bg-gray-100 text-gray-700 rounded-lg text-xs font-medium hover:bg-gray-200 flex items-center gap-1">
                <i data-lucide="download" class="w-3 h-3"></i> Download
              </a>
              <?php endif; ?>
              <button onclick="document.getElementById('contract-modal-<?= $l['id'] ?>').classList.remove('hidden')"
                class="px-3 py-1.5 bg-indigo-100 text-indigo-700 rounded-lg text-xs font-medium hover:bg-indigo-200">
                <?= $l['contract_pdf'] ? 'Update' : 'Upload' ?> Contract
              </button>
            </div>
          </div>
        </div>

        <!-- Contract upload modal per lecturer -->
        <div id="contract-modal-<?= $l['id'] ?>" class="hidden fixed inset-0 z-50 flex items-center justify-center p-4" style="background:rgba(0,0,0,0.5);">
          <div class="bg-white rounded-2xl w-full max-w-md shadow-xl">
            <div class="flex items-center justify-between px-6 py-4 border-b border-gray-100">
              <h3 class="font-semibold text-gray-900">Upload Contract — <?= htmlspecialchars($l['full_name']) ?></h3>
              <button onclick="document.getElementById('contract-modal-<?= $l['id'] ?>').classList.add('hidden')" class="text-gray-400 hover:text-gray-600"><i data-lucide="x" class="w-5 h-5"></i></button>
            </div>
            <form method="POST" enctype="multipart/form-data" class="p-6 space-y-4">
              <input type="hidden" name="action" value="upload_contract">
              <input type="hidden" name="lecturer_id" value="<?= $l['id'] ?>">
              <div class="grid grid-cols-2 gap-4">
                <div>
                  <label class="block text-sm font-medium text-gray-700 mb-1.5">Start date</label>
                  <input type="date" name="contract_start" value="<?= $l['contract_start'] ?>" class="w-full px-4 py-3 border border-gray-200 rounded-xl text-sm focus:outline-none focus:ring-2 focus:ring-indigo-500" />
                </div>
                <div>
                  <label class="block text-sm font-medium text-gray-700 mb-1.5">End date</label>
                  <input type="date" name="contract_end" value="<?= $l['contract_end'] ?>" class="w-full px-4 py-3 border border-gray-200 rounded-xl text-sm focus:outline-none focus:ring-2 focus:ring-indigo-500" />
                </div>
              </div>
              <div>
                <label class="block text-sm font-medium text-gray-700 mb-1.5">Contract PDF</label>
                <input type="file" name="contract" accept=".pdf" class="w-full px-4 py-3 border border-gray-200 rounded-xl text-sm" />
              </div>
              <div class="flex gap-3">
                <button type="button" onclick="document.getElementById('contract-modal-<?= $l['id'] ?>').classList.add('hidden')" class="flex-1 px-4 py-3 border border-gray-200 rounded-xl text-sm text-gray-700 hover:bg-gray-50">Cancel</button>
                <button type="submit" class="flex-1 bg-indigo-600 text-white px-4 py-3 rounded-xl text-sm font-medium hover:bg-indigo-700">Save</button>
              </div>
            </form>
          </div>
        </div>
        <?php endforeach; ?>
      </div>
      <?php endif; ?>
    </main>
  </div>
</div>

<!-- LEAVE DECISION MODAL -->
<div id="leave-modal" class="hidden fixed inset-0 z-50 flex items-center justify-center p-4" style="background:rgba(0,0,0,0.5);">
  <div class="bg-white rounded-2xl w-full max-w-md shadow-xl">
    <div class="flex items-center justify-between px-6 py-4 border-b border-gray-100">
      <h3 class="font-semibold text-gray-900" id="leave-modal-title">Approve Leave</h3>
      <button onclick="document.getElementById('leave-modal').classList.add('hidden')" class="text-gray-400 hover:text-gray-600"><i data-lucide="x" class="w-5 h-5"></i></button>
    </div>
    <form method="POST" class="p-6 space-y-4">
      <input type="hidden" name="action" value="decide_leave">
      <input type="hidden" name="leave_id" id="leave-id">
      <input type="hidden" name="status" id="leave-status">
      <div>
        <label class="block text-sm font-medium text-gray-700 mb-1.5">Note (optional)</label>
        <textarea name="note" rows="3" placeholder="Add a note for the lecturer..."
          class="w-full px-4 py-3 border border-gray-200 rounded-xl text-sm focus:outline-none focus:ring-2 focus:ring-indigo-500 resize-none"></textarea>
      </div>
      <div class="flex gap-3">
        <button type="button" onclick="document.getElementById('leave-modal').classList.add('hidden')" class="flex-1 px-4 py-3 border border-gray-200 rounded-xl text-sm text-gray-700 hover:bg-gray-50">Cancel</button>
        <button type="submit" id="leave-submit-btn" class="flex-1 bg-indigo-600 text-white px-4 py-3 rounded-xl text-sm font-medium hover:bg-indigo-700">Confirm</button>
      </div>
    </form>
  </div>
</div>

<!-- HR DECISION MODAL -->
<div id="hr-modal" class="hidden fixed inset-0 z-50 flex items-center justify-center p-4" style="background:rgba(0,0,0,0.5);">
  <div class="bg-white rounded-2xl w-full max-w-md shadow-xl">
    <div class="flex items-center justify-between px-6 py-4 border-b border-gray-100">
      <h3 class="font-semibold text-gray-900">Respond to HR Form</h3>
      <button onclick="document.getElementById('hr-modal').classList.add('hidden')" class="text-gray-400 hover:text-gray-600"><i data-lucide="x" class="w-5 h-5"></i></button>
    </div>
    <form method="POST" class="p-6 space-y-4">
      <input type="hidden" name="action" value="decide_hr">
      <input type="hidden" name="form_id" id="hr-form-id">
      <div>
        <label class="block text-sm font-medium text-gray-700 mb-1.5">Update status</label>
        <select name="status" class="w-full px-4 py-3 border border-gray-200 rounded-xl text-sm focus:outline-none focus:ring-2 focus:ring-indigo-500">
          <option value="in_review">In review</option>
          <option value="resolved">Resolved</option>
          <option value="rejected">Rejected</option>
        </select>
      </div>
      <div>
        <label class="block text-sm font-medium text-gray-700 mb-1.5">Admin note</label>
        <textarea name="note" rows="3" class="w-full px-4 py-3 border border-gray-200 rounded-xl text-sm focus:outline-none focus:ring-2 focus:ring-indigo-500 resize-none"></textarea>
      </div>
      <div class="flex gap-3">
        <button type="button" onclick="document.getElementById('hr-modal').classList.add('hidden')" class="flex-1 px-4 py-3 border border-gray-200 rounded-xl text-sm text-gray-700 hover:bg-gray-50">Cancel</button>
        <button type="submit" class="flex-1 bg-indigo-600 text-white px-4 py-3 rounded-xl text-sm font-medium hover:bg-indigo-700">Save</button>
      </div>
    </form>
  </div>
</div>

<?php require_once $_SERVER['DOCUMENT_ROOT'] . '/ByteClass/includes/scripts.php'; ?>
<script>
function decideLeave(id, status) {
  document.getElementById('leave-id').value     = id;
  document.getElementById('leave-status').value = status;
  document.getElementById('leave-modal-title').textContent = status === 'approved' ? 'Approve Leave Request' : 'Reject Leave Request';
  document.getElementById('leave-submit-btn').className = 'flex-1 ' + (status === 'approved' ? 'bg-green-600 hover:bg-green-700' : 'bg-red-600 hover:bg-red-700') + ' text-white px-4 py-3 rounded-xl text-sm font-medium';
  document.getElementById('leave-modal').classList.remove('hidden');
  lucide.createIcons();
}
function decideHR(id) {
  document.getElementById('hr-form-id').value = id;
  document.getElementById('hr-modal').classList.remove('hidden');
  lucide.createIcons();
}
</script>
<?php require_once $_SERVER['DOCUMENT_ROOT'] . '/ByteClass/includes/footer-minimal.php'; ?>