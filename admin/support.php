<?php
$required_role = 'admin';
require_once $_SERVER['DOCUMENT_ROOT'] . '/ByteClass/includes/auth-check.php';
require_once $_SERVER['DOCUMENT_ROOT'] . '/ByteClass/config/constants.php';
require_once $_SERVER['DOCUMENT_ROOT'] . '/ByteClass/config/database.php';
require_once $_SERVER['DOCUMENT_ROOT'] . '/ByteClass/helpers/response.php';

$db = Database::getInstance()->getConnection();
$success_msg = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $action = $_POST['action'] ?? '';

    if ($action === 'reply') {
        $tid     = (int)($_POST['ticket_id'] ?? 0);
        $message = sanitize($_POST['message'] ?? '');
        $status  = sanitize($_POST['status'] ?? '');

        if ($tid && $message) {
            $db->prepare("INSERT INTO ticket_replies (ticket_id, user_id, message) VALUES (?,?,?)")
               ->execute([$tid, $_SESSION['user_id'], $message]);

            if ($status) {
                $auto_close = $status === 'resolved' ? date('Y-m-d H:i:s', strtotime('+7 days')) : null;
                $db->prepare("UPDATE support_tickets SET status=?, auto_close_at=?, updated_at=NOW() WHERE id=?")
                   ->execute([$status, $auto_close, $tid]);
            }

            // Notify user
            $t = $db->prepare("SELECT user_id, subject FROM support_tickets WHERE id=?");
            $t->execute([$tid]);
            $ticket = $t->fetch();
            if ($ticket) {
                $db->prepare("INSERT INTO notifications (user_id, title, message, type) VALUES (?,?,?,?)")
                   ->execute([$ticket['user_id'], 'Support Ticket Reply', "Admin replied to your ticket: {$ticket['subject']}", 'support']);
            }
            $success_msg = 'Reply sent.';
        }
    }

    if ($action === 'assign') {
        $tid  = (int)($_POST['ticket_id'] ?? 0);
        $aid  = (int)($_POST['admin_id'] ?? 0);
        $db->prepare("UPDATE support_tickets SET assigned_to=?, updated_at=NOW() WHERE id=?")->execute([$aid, $tid]);
        $success_msg = 'Ticket assigned.';
    }

    if ($action === 'priority') {
        $tid  = (int)($_POST['ticket_id'] ?? 0);
        $prio = sanitize($_POST['priority'] ?? '');
        if (in_array($prio, ['low','medium','high','urgent'])) {
            $db->prepare("UPDATE support_tickets SET priority=?, updated_at=NOW() WHERE id=?")->execute([$prio, $tid]);
            if ($prio === 'urgent') {
                foreach ($db->query("SELECT id FROM users WHERE role='admin'")->fetchAll() as $a) {
                    $db->prepare("INSERT INTO notifications (user_id, title, message, type) VALUES (?,?,?,?)")
                       ->execute([$a['id'], 'Urgent Ticket', 'A ticket has been marked as urgent.', 'support']);
                }
            }
            $success_msg = 'Priority updated.';
        }
    }
}

$status_filter   = sanitize($_GET['status']   ?? '');
$category_filter = sanitize($_GET['category'] ?? '');
$page   = max(1,(int)($_GET['page'] ?? 1));
$limit  = 20;
$offset = ($page-1)*$limit;

$where  = ['1=1'];
$params = [];
if ($status_filter)   { $where[] = 'st.status = ?';   $params[] = $status_filter; }
if ($category_filter) { $where[] = 'st.category = ?'; $params[] = $category_filter; }
$wsql = implode(' AND ', $where);

$cnt = $db->prepare("SELECT COUNT(*) FROM support_tickets st WHERE $wsql");
$cnt->execute($params);
$total       = (int)$cnt->fetchColumn();
$total_pages = max(1,ceil($total/$limit));

$stmt = $db->prepare("
    SELECT st.*, u.full_name, u.email, u.role AS user_role,
           a.full_name AS assigned_name
    FROM support_tickets st
    JOIN users u ON u.id = st.user_id
    LEFT JOIN users a ON a.id = st.assigned_to
    WHERE $wsql
    ORDER BY FIELD(st.priority,'urgent','high','medium','low'), st.created_at DESC
    LIMIT $limit OFFSET $offset
");
$stmt->execute($params);
$tickets = $stmt->fetchAll();

$admins = $db->query("SELECT id, full_name FROM users WHERE role='admin'")->fetchAll();
$open_count   = $db->query("SELECT COUNT(*) FROM support_tickets WHERE status='open'")->fetchColumn();
$urgent_count = $db->query("SELECT COUNT(*) FROM support_tickets WHERE priority='urgent' AND status != 'closed'")->fetchColumn();

// Selected ticket for thread view
$selected_id = (int)($_GET['ticket'] ?? 0);
$selected_ticket = null;
$ticket_replies  = [];
if ($selected_id) {
    $t = $db->prepare("SELECT st.*, u.full_name, u.email FROM support_tickets st JOIN users u ON u.id=st.user_id WHERE st.id=?");
    $t->execute([$selected_id]);
    $selected_ticket = $t->fetch();

    $r = $db->prepare("SELECT tr.*, u.full_name, u.role FROM ticket_replies tr JOIN users u ON u.id=tr.user_id WHERE tr.ticket_id=? ORDER BY tr.created_at ASC");
    $r->execute([$selected_id]);
    $ticket_replies = $r->fetchAll();
}

$page_title = 'Support Tickets';
require_once $_SERVER['DOCUMENT_ROOT'] . '/ByteClass/includes/head.php';
?>
<div class="flex min-h-screen bg-gray-50">
  <?php require_once $_SERVER['DOCUMENT_ROOT'] . '/ByteClass/includes/sidebar-admin.php'; ?>
  <div id="main-content" class="flex-1 flex flex-col" style="margin-left:256px;">
<?php require_once $_SERVER['DOCUMENT_ROOT'] . '/ByteClass/includes/navbar-admin.php'; ?>
    <main class="flex-1 p-6">
      <div class="bg-gradient-to-r from-indigo-600 to-cyan-500 rounded-2xl px-6 py-5 mb-6">
        <h2 class="text-white text-xl font-bold">Support Tickets</h2>
        <p class="text-indigo-100 text-sm mt-0.5">Manage and respond to student and lecturer tickets</p>
      </div>

      <?php if ($success_msg): ?><div class="mb-4 p-4 bg-green-50 border border-green-200 rounded-xl text-sm text-green-700 flex gap-3"><i data-lucide="check-circle" class="w-5 h-5 flex-shrink-0"></i><?= htmlspecialchars($success_msg) ?></div><?php endif; ?>

      <div class="grid grid-cols-2 lg:grid-cols-4 gap-4 mb-6">
        <?php foreach ([
          ['label'=>'Open tickets', 'value'=>$open_count,   'color'=>'bg-indigo-500','icon'=>'inbox'],
          ['label'=>'Urgent',       'value'=>$urgent_count, 'color'=>'bg-red-500',   'icon'=>'alert-triangle'],
          ['label'=>'Total',        'value'=>$total,        'color'=>'bg-gray-500',  'icon'=>'ticket'],
          ['label'=>'Resolved',     'value'=>$db->query("SELECT COUNT(*) FROM support_tickets WHERE status='resolved'")->fetchColumn(),'color'=>'bg-green-500','icon'=>'check-circle'],
        ] as $s): ?>
        <div class="bg-white rounded-2xl p-4 border border-gray-100 flex items-center gap-3">
          <div class="w-10 h-10 <?= $s['color'] ?> rounded-xl flex items-center justify-center"><i data-lucide="<?= $s['icon'] ?>" class="w-5 h-5 text-white"></i></div>
          <div><p class="text-xl font-bold text-gray-900"><?= number_format($s['value']) ?></p><p class="text-xs text-gray-500"><?= $s['label'] ?></p></div>
        </div>
        <?php endforeach; ?>
      </div>

      <div class="grid grid-cols-1 lg:grid-cols-2 gap-6">
        <!-- Ticket list -->
        <div>
          <form method="GET" class="bg-white rounded-2xl border border-gray-100 p-3 mb-4 flex gap-2">
            <select name="status" class="flex-1 px-3 py-2 border border-gray-200 rounded-xl text-sm focus:outline-none focus:ring-2 focus:ring-indigo-500">
              <option value="">All statuses</option>
              <?php foreach (['open','in_progress','resolved','closed'] as $st): ?><option value="<?= $st ?>" <?= $status_filter===$st?'selected':'' ?>><?= ucfirst(str_replace('_',' ',$st)) ?></option><?php endforeach; ?>
            </select>
            <select name="category" class="flex-1 px-3 py-2 border border-gray-200 rounded-xl text-sm focus:outline-none focus:ring-2 focus:ring-indigo-500">
              <option value="">All categories</option>
              <?php foreach (['technical','payment','course_content','account','lecturer_support','other'] as $cat): ?><option value="<?= $cat ?>" <?= $category_filter===$cat?'selected':'' ?>><?= ucwords(str_replace('_',' ',$cat)) ?></option><?php endforeach; ?>
            </select>
            <button type="submit" class="bg-indigo-600 text-white px-3 py-2 rounded-xl text-sm hover:bg-indigo-700"><i data-lucide="filter" class="w-4 h-4"></i></button>
          </form>

          <div class="space-y-2">
            <?php if (empty($tickets)): ?>
            <div class="bg-white rounded-2xl border border-gray-100 p-8 text-center text-gray-400 text-sm">No tickets found</div>
            <?php else: foreach ($tickets as $t):
              $pc = match($t['priority']) { 'urgent'=>'border-l-red-500','high'=>'border-l-orange-400','medium'=>'border-l-amber-400',default=>'border-l-gray-300'};
              $sc = match($t['status']) { 'open'=>'bg-indigo-100 text-indigo-700','in_progress'=>'bg-blue-100 text-blue-700','resolved'=>'bg-green-100 text-green-700',default=>'bg-gray-100 text-gray-600'};
            ?>
            <a href="?ticket=<?= $t['id'] ?>&status=<?= urlencode($status_filter) ?>&category=<?= urlencode($category_filter) ?>"
               class="block bg-white rounded-xl border-l-4 <?= $pc ?> border border-gray-100 p-4 hover:shadow-sm transition-shadow <?= $selected_id===$t['id']?'ring-2 ring-indigo-500':'' ?>">
              <div class="flex items-start justify-between gap-2">
                <div class="flex-1 min-w-0">
                  <p class="text-sm font-semibold text-gray-900 truncate"><?= htmlspecialchars($t['subject']) ?></p>
                  <p class="text-xs text-gray-500 mt-0.5"><?= htmlspecialchars($t['full_name']) ?> · <?= htmlspecialchars($t['ticket_uid']) ?></p>
                </div>
                <span class="text-xs px-2 py-0.5 rounded-full font-medium <?= $sc ?> flex-shrink-0"><?= ucfirst(str_replace('_',' ',$t['status'])) ?></span>
              </div>
              <div class="flex items-center gap-3 mt-2 text-xs text-gray-400">
                <span><?= ucwords(str_replace('_',' ',$t['category'])) ?></span>
                <span><?= date('M d, Y', strtotime($t['created_at'])) ?></span>
                <?php if ($t['assigned_name']): ?><span>→ <?= htmlspecialchars($t['assigned_name']) ?></span><?php endif; ?>
              </div>
            </a>
            <?php endforeach; endif; ?>
          </div>

          <?php if ($total_pages > 1): ?>
          <div class="flex gap-2 mt-4 justify-center">
            <?php if ($page>1): ?><a href="?page=<?= $page-1 ?>&status=<?= urlencode($status_filter) ?>&category=<?= urlencode($category_filter) ?>" class="px-3 py-1.5 border border-gray-200 rounded-lg text-sm hover:bg-gray-50">Previous</a><?php endif; ?>
            <?php if ($page<$total_pages): ?><a href="?page=<?= $page+1 ?>&status=<?= urlencode($status_filter) ?>&category=<?= urlencode($category_filter) ?>" class="px-3 py-1.5 border border-gray-200 rounded-lg text-sm hover:bg-gray-50">Next</a><?php endif; ?>
          </div>
          <?php endif; ?>
        </div>

        <!-- Ticket thread -->
        <div>
          <?php if ($selected_ticket): ?>
          <div class="bg-white rounded-2xl border border-gray-100 overflow-hidden">
            <div class="px-5 py-4 border-b border-gray-100">
              <h3 class="font-semibold text-gray-900"><?= htmlspecialchars($selected_ticket['subject']) ?></h3>
              <div class="flex items-center gap-3 mt-1 text-xs text-gray-500">
                <span><?= htmlspecialchars($selected_ticket['full_name']) ?></span>
                <span><?= htmlspecialchars($selected_ticket['ticket_uid']) ?></span>
                <span><?= ucfirst(str_replace('_',' ',$selected_ticket['category'])) ?></span>
              </div>
              <div class="flex items-center gap-2 mt-2">
                <form method="POST" class="flex gap-2">
                  <input type="hidden" name="action" value="priority">
                  <input type="hidden" name="ticket_id" value="<?= $selected_ticket['id'] ?>">
                  <select name="priority" onchange="this.form.submit()" class="px-3 py-1 border border-gray-200 rounded-lg text-xs focus:outline-none">
                    <?php foreach (['low','medium','high','urgent'] as $p): ?><option value="<?= $p ?>" <?= $selected_ticket['priority']===$p?'selected':'' ?>><?= ucfirst($p) ?></option><?php endforeach; ?>
                  </select>
                </form>
                <form method="POST" class="flex gap-2">
                  <input type="hidden" name="action" value="assign">
                  <input type="hidden" name="ticket_id" value="<?= $selected_ticket['id'] ?>">
                  <select name="admin_id" onchange="this.form.submit()" class="px-3 py-1 border border-gray-200 rounded-lg text-xs focus:outline-none">
                    <option value="">Assign to...</option>
                    <?php foreach ($admins as $a): ?><option value="<?= $a['id'] ?>" <?= $selected_ticket['assigned_to']===$a['id']?'selected':'' ?>><?= htmlspecialchars($a['full_name']) ?></option><?php endforeach; ?>
                  </select>
                </form>
              </div>
            </div>

            <!-- Original message -->
            <div class="px-5 py-4 bg-gray-50 border-b border-gray-100">
              <p class="text-sm text-gray-700"><?= htmlspecialchars($selected_ticket['description']) ?></p>
            </div>

            <!-- Replies -->
            <div class="px-5 py-4 space-y-4 max-h-64 overflow-y-auto">
              <?php foreach ($ticket_replies as $r):
                $is_admin = in_array($r['role'],['admin']);
              ?>
              <div class="flex gap-3 <?= $is_admin ? 'flex-row-reverse' : '' ?>">
                <div class="w-8 h-8 <?= $is_admin ? 'bg-indigo-500' : 'bg-gray-300' ?> rounded-full flex items-center justify-center text-white text-xs font-bold flex-shrink-0">
                  <?= strtoupper(substr($r['full_name'],0,1)) ?>
                </div>
                <div class="<?= $is_admin ? 'bg-indigo-600 text-white' : 'bg-gray-100 text-gray-800' ?> rounded-xl px-4 py-2.5 max-w-xs">
                  <p class="text-xs font-semibold mb-1"><?= htmlspecialchars($r['full_name']) ?></p>
                  <p class="text-sm"><?= htmlspecialchars($r['message']) ?></p>
                  <p class="text-xs opacity-70 mt-1"><?= date('M d, H:i', strtotime($r['created_at'])) ?></p>
                </div>
              </div>
              <?php endforeach; ?>
            </div>

            <!-- Reply form -->
            <?php if ($selected_ticket['status'] !== 'closed'): ?>
            <form method="POST" class="px-5 py-4 border-t border-gray-100">
              <input type="hidden" name="action" value="reply">
              <input type="hidden" name="ticket_id" value="<?= $selected_ticket['id'] ?>">
              <textarea name="message" required rows="2" placeholder="Type your reply..."
                class="w-full px-4 py-3 border border-gray-200 rounded-xl text-sm focus:outline-none focus:ring-2 focus:ring-indigo-500 resize-none mb-3"></textarea>
              <div class="flex gap-2">
                <select name="status" class="flex-1 px-3 py-2 border border-gray-200 rounded-xl text-sm focus:outline-none focus:ring-2 focus:ring-indigo-500">
                  <option value="">Keep current status</option>
                  <option value="in_progress">Mark in progress</option>
                  <option value="resolved">Mark resolved</option>
                  <option value="closed">Close ticket</option>
                </select>
                <button type="submit" class="bg-indigo-600 text-white px-4 py-2 rounded-xl text-sm font-medium hover:bg-indigo-700 flex items-center gap-2">
                  <i data-lucide="send" class="w-4 h-4"></i> Reply
                </button>
              </div>
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
  </div>
</div>
<?php require_once $_SERVER['DOCUMENT_ROOT'] . '/ByteClass/includes/scripts.php'; ?>
<?php require_once $_SERVER['DOCUMENT_ROOT'] . '/ByteClass/includes/footer-minimal.php'; ?>


