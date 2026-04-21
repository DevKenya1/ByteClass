<?php
$required_role = 'lecturer';
require_once $_SERVER['DOCUMENT_ROOT'] . '/ByteClass/includes/auth-check.php';
require_once $_SERVER['DOCUMENT_ROOT'] . '/ByteClass/config/constants.php';
require_once $_SERVER['DOCUMENT_ROOT'] . '/ByteClass/config/database.php';
require_once $_SERVER['DOCUMENT_ROOT'] . '/ByteClass/helpers/response.php';
$db = Database::getInstance()->getConnection();
$id = (int)$_SESSION['user_id'];

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $message = sanitize($_POST['message'] ?? '');
    if ($message && strlen($message) <= 500) {
        $db->prepare("INSERT INTO community_messages (user_id, message) VALUES (?,?)")->execute([$id, $message]);
        header('Location: ' . APP_URL . '/lecturer/community.php'); exit;
    }
}

$msgs = $db->query("
    SELECT cm.id, cm.message, cm.created_at, u.full_name, u.profile_photo, u.role
    FROM community_messages cm JOIN users u ON u.id=cm.user_id
    WHERE cm.is_deleted=0 AND cm.created_at >= DATE_SUB(NOW(), INTERVAL 24 HOUR)
    ORDER BY cm.created_at DESC LIMIT 100
")->fetchAll();

$page_title = 'Community';
require_once $_SERVER['DOCUMENT_ROOT'] . '/ByteClass/includes/head.php';
?>
<div class="flex min-h-screen bg-gray-50">
  <?php require_once $_SERVER['DOCUMENT_ROOT'] . '/ByteClass/includes/sidebar-lecturer.php'; ?>
  <div id="main-content" class="flex-1 flex flex-col" style="margin-left:256px;">
    <?php require_once $_SERVER['DOCUMENT_ROOT'] . '/ByteClass/includes/navbar-lecturer.php'; ?>
    <main class="flex-1 p-6 flex flex-col">
      <div class="bg-gradient-to-r from-cyan-600 to-indigo-600 rounded-2xl px-6 py-5 mb-6 flex items-center justify-between">
        <div><h2 class="text-white text-xl font-bold">Community Chat</h2><p class="text-cyan-100 text-sm mt-0.5">Platform-wide chat · Messages auto-delete after 24 hours</p></div>
        <span class="bg-white/20 text-white text-xs px-3 py-1.5 rounded-full"><?= count($msgs) ?> active messages</span>
      </div>
      <div class="bg-white rounded-2xl border border-gray-100 flex-1 flex flex-col overflow-hidden" style="max-height:62vh;">
        <div class="flex-1 overflow-y-auto p-5 space-y-4" id="chat-box">
          <?php if (empty($msgs)): ?>
          <div class="text-center py-12"><i data-lucide="message-square" class="w-10 h-10 text-gray-300 mx-auto mb-3"></i><p class="text-gray-400 text-sm">No recent messages. Start the conversation!</p></div>
          <?php else: foreach (array_reverse($msgs) as $m):
            $is_me = (int)$m['id'] === $id;
            $color = match($m['role']) { 'admin'=>'bg-indigo-500','lecturer'=>'bg-cyan-500',default=>'bg-gray-400'};
          ?>
          <div class="flex gap-3 <?= $is_me ? 'flex-row-reverse' : '' ?>">
            <?php if ($m['profile_photo']): ?><img src="<?= htmlspecialchars($m['profile_photo']) ?>" class="w-8 h-8 rounded-full object-cover flex-shrink-0" />
            <?php else: ?><div class="w-8 h-8 <?= $color ?> rounded-full flex items-center justify-center text-white text-xs font-bold flex-shrink-0"><?= strtoupper(substr($m['full_name'],0,1)) ?></div><?php endif; ?>
            <div class="flex flex-col <?= $is_me ? 'items-end' : 'items-start' ?> gap-0.5 max-w-sm">
              <?php if (!$is_me): ?>
              <div class="flex items-center gap-1.5">
                <span class="text-xs font-semibold text-gray-700"><?= htmlspecialchars(explode(' ',$m['full_name'])[0]) ?></span>
                <?php if ($m['role']==='admin'): ?><span class="text-xs bg-indigo-100 text-indigo-700 px-1.5 py-0.5 rounded-full">Admin</span><?php elseif($m['role']==='lecturer'): ?><span class="text-xs bg-cyan-100 text-cyan-700 px-1.5 py-0.5 rounded-full">Lecturer</span><?php endif; ?>
              </div>
              <?php endif; ?>
              <div class="px-4 py-2.5 rounded-2xl text-sm <?= $is_me ? 'bg-indigo-600 text-white rounded-tr-sm' : 'bg-gray-100 text-gray-800 rounded-tl-sm' ?>"><?= nl2br(htmlspecialchars($m['message'])) ?></div>
              <span class="text-xs text-gray-400"><?= time_ago($m['created_at']) ?></span>
            </div>
          </div>
          <?php endforeach; endif; ?>
        </div>
        <form method="POST" class="border-t border-gray-100 p-4 flex gap-3">
          <input type="text" name="message" id="msg-input" required maxlength="500" placeholder="Type a message..." autocomplete="off"
            class="flex-1 px-4 py-3 border border-gray-200 rounded-xl text-sm focus:outline-none focus:ring-2 focus:ring-indigo-500" />
          <button type="submit" class="bg-indigo-600 text-white px-5 py-3 rounded-xl text-sm font-semibold hover:bg-indigo-700 flex items-center gap-2">
            <i data-lucide="send" class="w-4 h-4"></i> Send
          </button>
        </form>
      </div>
    </main>
    <?php require_once $_SERVER['DOCUMENT_ROOT'] . '/ByteClass/includes/footer-minimal.php'; ?>
  </div>
</div>
<script>document.getElementById('chat-box').scrollTop = document.getElementById('chat-box').scrollHeight;</script>
<?php require_once $_SERVER['DOCUMENT_ROOT'] . '/ByteClass/includes/scripts.php'; ?>
