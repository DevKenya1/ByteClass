<?php
$required_role = 'student';
require_once $_SERVER['DOCUMENT_ROOT'] . '/ByteClass/includes/auth-check.php';
require_once $_SERVER['DOCUMENT_ROOT'] . '/ByteClass/config/constants.php';
require_once $_SERVER['DOCUMENT_ROOT'] . '/ByteClass/config/database.php';
require_once $_SERVER['DOCUMENT_ROOT'] . '/ByteClass/helpers/response.php';

$db = Database::getInstance()->getConnection();
$id = (int)$_SESSION['user_id'];
$success_msg = $error_msg = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $action = $_POST['action'] ?? '';
    if ($action === 'create_ticket') {
        $subject  = sanitize($_POST['subject']  ?? '');
        $category = sanitize($_POST['category'] ?? 'other');
        $desc     = sanitize($_POST['description'] ?? '');
        if (!$subject || !$desc) {
            $error_msg = 'Subject and description are required.';
        } else {
            $uid  = TICKET_PREFIX . '-' . strtoupper(substr(bin2hex(random_bytes(4)),0,8));
            $auto = date('Y-m-d H:i:s', strtotime('+7 days'));
            $db->prepare("INSERT INTO support_tickets (ticket_uid,user_id,subject,category,description,auto_close_at) VALUES (?,?,?,?,?,?)")
               ->execute([$uid,$id,$subject,$category,$desc,$auto]);
            $db->prepare("INSERT INTO notifications (user_id,title,message,type) VALUES (?,?,?,?)")
               ->execute([$id,'Ticket Created',"Your support ticket $uid has been submitted.",'support']);
            // Notify admins
            foreach ($db->query("SELECT id FROM users WHERE role='admin'")->fetchAll() as $a) {
                $db->prepare("INSERT INTO notifications (user_id,title,message,type) VALUES (?,?,?,?)")
                   ->execute([$a['id'],'New Support Ticket',"New ticket from {$_SESSION['full_name']}: $subject",'support']);
            }
            $success_msg = "Ticket $uid submitted. We will respond shortly.";
        }
    }
    if ($action === 'reply') {
        $tid = (int)($_POST['ticket_id'] ?? 0);
        $msg = sanitize($_POST['message'] ?? '');
        if ($tid && $msg) {
            // Verify ticket belongs to this student
            $check = $db->prepare("SELECT id FROM support_tickets WHERE id=? AND user_id=?");
            $check->execute([$tid,$id]);
            if ($check->fetch()) {
                $db->prepare("INSERT INTO ticket_replies (ticket_id,user_id,message) VALUES (?,?,?)")->execute([$tid,$id,$msg]);
                $db->prepare("UPDATE support_tickets SET status='open',updated_at=NOW() WHERE id=?")->execute([$tid]);
                $success_msg = 'Reply sent.';
            }
        }
    }
}

$selected_id = (int)($_GET['ticket'] ?? 0);
$tickets = $db->prepare("SELECT * FROM support_tickets WHERE user_id=? ORDER BY created_at DESC");
$tickets->execute([$id]); $tickets = $tickets->fetchAll();

$selected_ticket = null;
$replies = [];
if ($selected_id) {
    $t = $db->prepare("SELECT * FROM support_tickets WHERE id=? AND user_id=?");
    $t->execute([$selected_id,$id]); $selected_ticket = $t->fetch();
    if ($selected_ticket) {
        $r = $db->prepare("SELECT tr.*,u.full_name,u.role FROM ticket_replies tr JOIN users u ON u.id=tr.user_id WHERE tr.ticket_id=? ORDER BY tr.created_at ASC");
        $r->execute([$selected_id]); $replies = $r->fetchAll();
    }
}

$page_title = 'Support';
require_once $_SERVER['DOCUMENT_ROOT'] . '/ByteClass/includes/head.php';
?>
<div class="flex min-h-screen bg-gray-50">
  <?php require_once $_SERVER['DOCUMENT_ROOT'] . '/ByteClass/includes/sidebar-student.php'; ?>
  <div id="main-content" class="flex-1 flex flex-col" style="margin-left:256px;">
    <?php require_once $_SERVER['DOCUMENT_ROOT'] . '/ByteClass/includes/navbar-student.php'; ?>
    <main class="flex-1 p-6">
      <div class="bg-gradient-to-r from-indigo-600 to-cyan-500 rounded-2xl px-6 py-5 mb-6 flex items-center justify-between">
        <div>
          <h2 class="text-white text-xl font-bold">Support</h2>
          <p class="text-indigo-100 text-sm mt-0.5">Get help from our support team</p>
        </div>
        <button onclick="document.getElementById('new-ticket-modal').classList.remove('hidden')"
          class="bg-white text-indigo-600 px-4 py-2 rounded-xl text-sm font-semibold hover:bg-indigo-50 flex items-center gap-2">
          <i data-lucide="plus" class="w-4 h-4"></i> New Ticket
        </button>
      </div>

      <?php if ($success_msg): ?><div class="mb-4 p-4 bg-green-50 border border-green-200 rounded-xl text-sm text-green-700 flex gap-3"><i data-lucide="check-circle" class="w-5 h-5 flex-shrink-0"></i><?= htmlspecialchars($success_msg) ?></div><?php endif; ?>
      <?php if ($error_msg): ?><div class="mb-4 p-4 bg-red-50 border border-red-200 rounded-xl text-sm text-red-700 flex gap-3"><i data-lucide="alert-circle" class="w-5 h-5 flex-shrink-0"></i><?= htmlspecialchars($error_msg) ?></div><?php endif; ?>

      <div class="grid grid-cols-1 lg:grid-cols-2 gap-6">
        <div class="space-y-3">
          <?php if (empty($tickets)): ?>
          <div class="bg-white rounded-2xl border border-gray-100 p-10 text-center">
            <i data-lucide="headphones" class="w-10 h-10 text-gray-300 mx-auto mb-3"></i>
            <p class="text-gray-400 text-sm">No support tickets yet</p>
          </div>
          <?php else: foreach ($tickets as $t):
            $sc = match($t['status']) { 'open'=>'bg-indigo-100 text-indigo-700','in_progress'=>'bg-blue-100 text-blue-700','resolved'=>'bg-green-100 text-green-700',default=>'bg-gray-100 text-gray-600'};
            $pc = match($t['priority']) { 'urgent'=>'border-l-red-500','high'=>'border-l-orange-400','medium'=>'border-l-amber-400',default=>'border-l-gray-300'};
          ?>
          <a href="?ticket=<?= $t['id'] ?>" class="block bg-white rounded-xl border-l-4 <?= $pc ?> border border-gray-100 p-4 hover:shadow-sm transition <?= $selected_id===$t['id']?'ring-2 ring-indigo-500':'' ?>">
            <div class="flex items-start justify-between gap-2">
              <div class="flex-1 min-w-0">
                <p class="text-sm font-semibold text-gray-900 truncate"><?= htmlspecialchars($t['subject']) ?></p>
                <p class="text-xs text-gray-400 mt-0.5"><?= htmlspecialchars($t['ticket_uid']) ?> · <?= ucwords(str_replace('_',' ',$t['category'])) ?></p>
              </div>
              <span class="text-xs px-2 py-0.5 rounded-full font-medium <?= $sc ?> flex-shrink-0"><?= ucfirst(str_replace('_',' ',$t['status'])) ?></span>
            </div>
            <p class="text-xs text-gray-400 mt-2"><?= date('M d, Y', strtotime($t['created_at'])) ?></p>
          </a>
          <?php endforeach; endif; ?>
        </div>

        <div>
          <?php if ($selected_ticket): ?>
          <div class="bg-white rounded-2xl border border-gray-100 overflow-hidden">
            <div class="px-5 py-4 border-b border-gray-100">
              <h3 class="font-semibold text-gray-900"><?= htmlspecialchars($selected_ticket['subject']) ?></h3>
              <div class="flex items-center gap-3 mt-1 text-xs text-gray-400">
                <span><?= htmlspecialchars($selected_ticket['ticket_uid']) ?></span>
                <span><?= ucwords(str_replace('_',' ',$selected_ticket['category'])) ?></span>
              </div>
            </div>
            <div class="px-5 py-4 bg-gray-50 border-b border-gray-100">
              <p class="text-sm text-gray-700"><?= htmlspecialchars($selected_ticket['description']) ?></p>
            </div>
            <div class="px-5 py-4 space-y-4 max-h-64 overflow-y-auto">
              <?php foreach ($replies as $r):
                $is_admin = in_array($r['role'],['admin','lecturer']);
              ?>
              <div class="flex gap-3 <?= $is_admin ? '' : 'flex-row-reverse' ?>">
                <div class="w-8 h-8 <?= $is_admin ? 'bg-indigo-500' : 'bg-gray-300' ?> rounded-full flex items-center justify-center text-white text-xs font-bold flex-shrink-0">
                  <?= strtoupper(substr($r['full_name'],0,1)) ?>
                </div>
                <div class="<?= $is_admin ? 'bg-gray-100 text-gray-800' : 'bg-indigo-600 text-white' ?> rounded-xl px-4 py-2.5 max-w-xs">
                  <p class="text-xs font-semibold mb-1"><?= $is_admin ? htmlspecialchars($r['full_name']) : 'You' ?></p>
                  <p class="text-sm"><?= htmlspecialchars($r['message']) ?></p>
                  <p class="text-xs opacity-70 mt-1"><?= date('M d, H:i', strtotime($r['created_at'])) ?></p>
                </div>
              </div>
              <?php endforeach; ?>
            </div>
            <?php if ($selected_ticket['status'] !== 'closed'): ?>
            <form method="POST" class="px-5 py-4 border-t border-gray-100">
              <input type="hidden" name="action" value="reply">
              <input type="hidden" name="ticket_id" value="<?= $selected_ticket['id'] ?>">
              <textarea name="message" required rows="2" placeholder="Type your reply..."
                class="w-full px-4 py-3 border border-gray-200 rounded-xl text-sm focus:outline-none focus:ring-2 focus:ring-indigo-500 resize-none mb-3"></textarea>
              <button type="submit" class="w-full bg-indigo-600 text-white py-2.5 rounded-xl text-sm font-medium hover:bg-indigo-700 flex items-center justify-center gap-2">
                <i data-lucide="send" class="w-4 h-4"></i> Send Reply
              </button>
            </form>
            <?php endif; ?>
          </div>
          <?php else: ?>
          <div class="bg-white rounded-2xl border border-gray-100 p-12 text-center">
            <i data-lucide="message-square" class="w-10 h-10 text-gray-300 mx-auto mb-3"></i>
            <p class="text-gray-400 text-sm">Select a ticket to view the conversation</p>
          </div>
          <?php endif; ?>
        </div>
      </div>
    </main>
    <?php require_once $_SERVER['DOCUMENT_ROOT'] . '/ByteClass/includes/footer-minimal.php'; ?>
  </div>
</div>

<!-- New Ticket Modal -->
<div id="new-ticket-modal" class="hidden fixed inset-0 z-50 flex items-center justify-center p-4" style="background:rgba(0,0,0,0.5);">
  <div class="bg-white rounded-2xl w-full max-w-lg shadow-xl">
    <div class="flex items-center justify-between px-6 py-4 border-b border-gray-100">
      <h3 class="font-semibold text-gray-900">New Support Ticket</h3>
      <button onclick="document.getElementById('new-ticket-modal').classList.add('hidden')" class="text-gray-400 hover:text-gray-600"><i data-lucide="x" class="w-5 h-5"></i></button>
    </div>
    <form method="POST" class="p-6 space-y-4">
      <input type="hidden" name="action" value="create_ticket">
      <div>
        <label class="block text-sm font-medium text-gray-700 mb-1.5">Subject *</label>
        <input type="text" name="subject" required placeholder="Brief description of your issue"
          class="w-full px-4 py-3 border border-gray-200 rounded-xl text-sm focus:outline-none focus:ring-2 focus:ring-indigo-500" />
      </div>
      <div>
        <label class="block text-sm font-medium text-gray-700 mb-1.5">Category</label>
        <select name="category" class="w-full px-4 py-3 border border-gray-200 rounded-xl text-sm focus:outline-none focus:ring-2 focus:ring-indigo-500">
          <option value="technical">Technical Issue</option>
          <option value="payment">Payment</option>
          <option value="course_content">Course Content</option>
          <option value="account">Account</option>
          <option value="other">Other</option>
        </select>
      </div>
      <div>
        <label class="block text-sm font-medium text-gray-700 mb-1.5">Description *</label>
        <textarea name="description" required rows="4" placeholder="Describe your issue in detail..."
          class="w-full px-4 py-3 border border-gray-200 rounded-xl text-sm focus:outline-none focus:ring-2 focus:ring-indigo-500 resize-none"></textarea>
      </div>
      <div class="flex gap-3">
        <button type="button" onclick="document.getElementById('new-ticket-modal').classList.add('hidden')"
          class="flex-1 px-4 py-3 border border-gray-200 rounded-xl text-sm text-gray-700 hover:bg-gray-50">Cancel</button>
        <button type="submit" class="flex-1 bg-indigo-600 text-white px-4 py-3 rounded-xl text-sm font-medium hover:bg-indigo-700 flex items-center justify-center gap-2">
          <i data-lucide="send" class="w-4 h-4"></i> Submit Ticket
        </button>
      </div>
    </form>
  </div>
</div>

<?php require_once $_SERVER['DOCUMENT_ROOT'] . '/ByteClass/includes/scripts.php'; ?>
