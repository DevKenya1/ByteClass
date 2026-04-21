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

    if ($action === 'create') {
        $title    = sanitize($_POST['title'] ?? '');
        $body     = sanitize($_POST['body'] ?? '');
        $audience = sanitize($_POST['audience'] ?? 'all');
        $is_offer = (int)($_POST['is_offer'] ?? 0);
        $discount = $is_offer ? (float)($_POST['discount_pct'] ?? 0) : null;
        $is_pinned= (int)($_POST['is_pinned'] ?? 0);
        $expires  = sanitize($_POST['expires_at'] ?? '');

        if (!$title || !$body || !$expires) {
            $error_msg = 'Title, body and expiry are required.';
        } else {
            $image = null;
            if (isset($_FILES['image']) && $_FILES['image']['error'] === 0) {
                $ext   = pathinfo($_FILES['image']['name'], PATHINFO_EXTENSION);
                $fname = 'ann_' . time() . '.' . $ext;
                $dest  = $_SERVER['DOCUMENT_ROOT'] . '/ByteClass/uploads/announcements/' . $fname;
                if (move_uploaded_file($_FILES['image']['tmp_name'], $dest)) {
                    $image = APP_URL . '/uploads/announcements/' . $fname;
                }
            }
            $db->prepare("INSERT INTO announcements (title, body, image, audience, is_offer, discount_pct, is_pinned, created_by, expires_at) VALUES (?,?,?,?,?,?,?,?,?)")
               ->execute([$title, $body, $image, $audience, $is_offer, $discount, $is_pinned, $_SESSION['user_id'], $expires]);
            $success_msg = 'Announcement created successfully.';
        }
    }

    if ($action === 'update') {
        $id    = (int)($_POST['ann_id'] ?? 0);
        $title = sanitize($_POST['title'] ?? '');
        $body  = sanitize($_POST['body'] ?? '');
        $expires = sanitize($_POST['expires_at'] ?? '');
        $db->prepare("UPDATE announcements SET title=?, body=?, expires_at=?, last_edited_by=?, updated_at=NOW() WHERE id=?")
           ->execute([$title, $body, $expires, $_SESSION['user_id'], $id]);
        $success_msg = 'Announcement updated.';
    }

    if ($action === 'pin') {
        $id  = (int)($_POST['ann_id'] ?? 0);
        $pin = (int)($_POST['pin'] ?? 0);
        $db->prepare("UPDATE announcements SET is_pinned=? WHERE id=?")->execute([$pin, $id]);
        $success_msg = $pin ? 'Announcement pinned.' : 'Announcement unpinned.';
    }
}

$announcements = $db->query("
    SELECT a.*, u.full_name AS created_by_name, e.full_name AS edited_by_name
    FROM announcements a
    LEFT JOIN users u ON u.id = a.created_by
    LEFT JOIN users e ON e.id = a.last_edited_by
    ORDER BY a.is_pinned DESC, a.created_at DESC
    LIMIT 50
")->fetchAll();

$page_title = 'Announcements';
require_once $_SERVER['DOCUMENT_ROOT'] . '/ByteClass/includes/head.php';
?>
<div class="flex min-h-screen bg-gray-50">
  <?php require_once $_SERVER['DOCUMENT_ROOT'] . '/ByteClass/includes/sidebar-admin.php'; ?>
  <div id="main-content" class="flex-1 flex flex-col" style="margin-left:256px;">
    <?php require_once $_SERVER['DOCUMENT_ROOT'] . '/ByteClass/includes/navbar-admin.php'; ?>
    <main class="flex-1 p-6">
      <div class="bg-gradient-to-r from-indigo-600 to-cyan-500 rounded-2xl px-6 py-5 mb-6 flex items-center justify-between">
        <div>
          <h2 class="text-white text-xl font-bold">Announcements</h2>
          <p class="text-indigo-100 text-sm mt-0.5">Create internal and external announcements</p>
        </div>
        <button onclick="document.getElementById('create-ann-modal').classList.remove('hidden')"
          class="bg-white text-indigo-600 px-4 py-2 rounded-xl font-semibold text-sm hover:bg-indigo-50 flex items-center gap-2">
          <i data-lucide="plus" class="w-4 h-4"></i> New
        </button>
      </div>

      <?php if ($success_msg): ?><div class="mb-4 p-4 bg-green-50 border border-green-200 rounded-xl text-sm text-green-700 flex gap-3"><i data-lucide="check-circle" class="w-5 h-5 flex-shrink-0"></i><?= htmlspecialchars($success_msg) ?></div><?php endif; ?>
      <?php if ($error_msg): ?><div class="mb-4 p-4 bg-red-50 border border-red-200 rounded-xl text-sm text-red-700 flex gap-3"><i data-lucide="alert-circle" class="w-5 h-5 flex-shrink-0"></i><?= htmlspecialchars($error_msg) ?></div><?php endif; ?>

      <div class="space-y-4">
        <?php if (empty($announcements)): ?>
        <div class="bg-white rounded-2xl border border-gray-100 p-12 text-center">
          <i data-lucide="megaphone" class="w-10 h-10 text-gray-300 mx-auto mb-3"></i>
          <p class="text-gray-400 text-sm">No announcements yet</p>
        </div>
        <?php else: foreach ($announcements as $a):
          $expired = strtotime($a['expires_at']) < time();
          $audience_colors = ['students'=>'bg-green-100 text-green-700','lecturers'=>'bg-cyan-100 text-cyan-700','internal_all'=>'bg-indigo-100 text-indigo-700','external'=>'bg-orange-100 text-orange-700','all'=>'bg-purple-100 text-purple-700'];
          $ac = $audience_colors[$a['audience']] ?? 'bg-gray-100 text-gray-600';
        ?>
        <div class="bg-white rounded-2xl border <?= $a['is_pinned'] ? 'border-indigo-200' : 'border-gray-100' ?> p-5 <?= $expired ? 'opacity-60' : '' ?>">
          <div class="flex items-start justify-between gap-4">
            <div class="flex-1">
              <div class="flex items-center gap-2 mb-2 flex-wrap">
                <?php if ($a['is_pinned']): ?><span class="bg-amber-100 text-amber-700 text-xs px-2 py-0.5 rounded-full font-medium flex items-center gap-1"><i data-lucide="pin" class="w-3 h-3"></i> Pinned</span><?php endif; ?>
                <?php if ($a['is_offer']): ?><span class="bg-red-100 text-red-700 text-xs px-2 py-0.5 rounded-full font-medium"><?= $a['discount_pct'] ?>% OFF</span><?php endif; ?>
                <span class="<?= $ac ?> text-xs px-2 py-0.5 rounded-full font-medium"><?= ucfirst(str_replace('_',' ',$a['audience'])) ?></span>
                <?php if ($expired): ?><span class="bg-gray-100 text-gray-500 text-xs px-2 py-0.5 rounded-full">Expired</span><?php endif; ?>
              </div>
              <h3 class="font-semibold text-gray-900 mb-1"><?= htmlspecialchars($a['title']) ?></h3>
              <p class="text-sm text-gray-600 mb-3"><?= htmlspecialchars($a['body']) ?></p>
              <div class="flex items-center gap-4 text-xs text-gray-400">
                <span>By <?= htmlspecialchars($a['created_by_name']) ?></span>
                <span>Created <?= date('M d, Y', strtotime($a['created_at'])) ?></span>
                <span class="<?= $expired ? 'text-red-400' : 'text-green-600' ?>">
                  Expires <?= date('M d, Y H:i', strtotime($a['expires_at'])) ?>
                </span>
                <?php if ($a['edited_by_name']): ?><span>Edited by <?= htmlspecialchars($a['edited_by_name']) ?></span><?php endif; ?>
              </div>
            </div>
            <div class="flex items-center gap-2 flex-shrink-0">
              <button onclick="editAnn(<?= $a['id'] ?>, <?= htmlspecialchars(json_encode($a['title'])) ?>, <?= htmlspecialchars(json_encode($a['body'])) ?>, '<?= $a['expires_at'] ?>')"
                class="p-2 text-gray-400 hover:text-indigo-600 hover:bg-indigo-50 rounded-lg transition-colors">
                <i data-lucide="edit" class="w-4 h-4"></i>
              </button>
              <form method="POST" class="inline">
                <input type="hidden" name="action" value="pin">
                <input type="hidden" name="ann_id" value="<?= $a['id'] ?>">
                <input type="hidden" name="pin" value="<?= $a['is_pinned'] ? 0 : 1 ?>">
                <button type="submit" class="p-2 text-gray-400 hover:text-amber-600 hover:bg-amber-50 rounded-lg transition-colors" title="<?= $a['is_pinned'] ? 'Unpin' : 'Pin' ?>">
                  <i data-lucide="pin" class="w-4 h-4"></i>
                </button>
              </form>
            </div>
          </div>
        </div>
        <?php endforeach; endif; ?>
      </div>
    </main>
  </div>
</div>

<!-- CREATE MODAL -->
<div id="create-ann-modal" class="hidden fixed inset-0 z-50 flex items-center justify-center p-4" style="background:rgba(0,0,0,0.5);">
  <div class="bg-white rounded-2xl w-full max-w-lg shadow-xl max-h-screen overflow-y-auto">
    <div class="flex items-center justify-between px-6 py-4 border-b border-gray-100 sticky top-0 bg-white">
      <h3 class="font-semibold text-gray-900">New Announcement</h3>
      <button onclick="document.getElementById('create-ann-modal').classList.add('hidden')" class="text-gray-400 hover:text-gray-600"><i data-lucide="x" class="w-5 h-5"></i></button>
    </div>
    <form method="POST" enctype="multipart/form-data" class="p-6 space-y-4">
      <input type="hidden" name="action" value="create">
      <div>
        <label class="block text-sm font-medium text-gray-700 mb-1.5">Title *</label>
        <input type="text" name="title" required class="w-full px-4 py-3 border border-gray-200 rounded-xl text-sm focus:outline-none focus:ring-2 focus:ring-indigo-500" />
      </div>
      <div>
        <label class="block text-sm font-medium text-gray-700 mb-1.5">Body *</label>
        <textarea name="body" required rows="4" class="w-full px-4 py-3 border border-gray-200 rounded-xl text-sm focus:outline-none focus:ring-2 focus:ring-indigo-500 resize-none"></textarea>
      </div>
      <div class="grid grid-cols-2 gap-4">
        <div>
          <label class="block text-sm font-medium text-gray-700 mb-1.5">Audience</label>
          <select name="audience" class="w-full px-4 py-3 border border-gray-200 rounded-xl text-sm focus:outline-none focus:ring-2 focus:ring-indigo-500">
            <option value="all">All</option>
            <option value="students">Students only</option>
            <option value="lecturers">Lecturers only</option>
            <option value="internal_all">Internal all</option>
            <option value="external">External (public)</option>
          </select>
        </div>
        <div>
          <label class="block text-sm font-medium text-gray-700 mb-1.5">Expires at *</label>
          <input type="datetime-local" name="expires_at" required class="w-full px-4 py-3 border border-gray-200 rounded-xl text-sm focus:outline-none focus:ring-2 focus:ring-indigo-500" />
        </div>
        <div>
          <label class="block text-sm font-medium text-gray-700 mb-1.5">Image (optional)</label>
          <input type="file" name="image" accept="image/*" class="w-full px-4 py-3 border border-gray-200 rounded-xl text-sm focus:outline-none" />
        </div>
        <div class="flex flex-col gap-3 pt-2">
          <label class="flex items-center gap-2 cursor-pointer">
            <input type="checkbox" name="is_pinned" value="1" class="w-4 h-4 text-indigo-600 rounded">
            <span class="text-sm text-gray-700">Pin this announcement</span>
          </label>
          <label class="flex items-center gap-2 cursor-pointer">
            <input type="checkbox" name="is_offer" value="1" id="is-offer-cb" class="w-4 h-4 text-indigo-600 rounded" onchange="document.getElementById('discount-field').classList.toggle('hidden',!this.checked)">
            <span class="text-sm text-gray-700">This is an offer</span>
          </label>
        </div>
      </div>
      <div id="discount-field" class="hidden">
        <label class="block text-sm font-medium text-gray-700 mb-1.5">Discount %</label>
        <input type="number" name="discount_pct" min="1" max="100" step="1" placeholder="e.g. 20"
          class="w-full px-4 py-3 border border-gray-200 rounded-xl text-sm focus:outline-none focus:ring-2 focus:ring-indigo-500" />
      </div>
      <div class="flex gap-3 pt-2">
        <button type="button" onclick="document.getElementById('create-ann-modal').classList.add('hidden')" class="flex-1 px-4 py-3 border border-gray-200 rounded-xl text-sm font-medium text-gray-700 hover:bg-gray-50">Cancel</button>
        <button type="submit" class="flex-1 bg-indigo-600 hover:bg-indigo-700 text-white px-4 py-3 rounded-xl text-sm font-medium flex items-center justify-center gap-2"><i data-lucide="send" class="w-4 h-4"></i> Publish</button>
      </div>
    </form>
  </div>
</div>

<!-- EDIT MODAL -->
<div id="edit-ann-modal" class="hidden fixed inset-0 z-50 flex items-center justify-center p-4" style="background:rgba(0,0,0,0.5);">
  <div class="bg-white rounded-2xl w-full max-w-lg shadow-xl">
    <div class="flex items-center justify-between px-6 py-4 border-b border-gray-100">
      <h3 class="font-semibold text-gray-900">Edit Announcement</h3>
      <button onclick="document.getElementById('edit-ann-modal').classList.add('hidden')" class="text-gray-400 hover:text-gray-600"><i data-lucide="x" class="w-5 h-5"></i></button>
    </div>
    <form method="POST" class="p-6 space-y-4">
      <input type="hidden" name="action" value="update">
      <input type="hidden" name="ann_id" id="edit-ann-id">
      <div>
        <label class="block text-sm font-medium text-gray-700 mb-1.5">Title *</label>
        <input type="text" name="title" id="edit-ann-title" required class="w-full px-4 py-3 border border-gray-200 rounded-xl text-sm focus:outline-none focus:ring-2 focus:ring-indigo-500" />
      </div>
      <div>
        <label class="block text-sm font-medium text-gray-700 mb-1.5">Body *</label>
        <textarea name="body" id="edit-ann-body" required rows="4" class="w-full px-4 py-3 border border-gray-200 rounded-xl text-sm focus:outline-none focus:ring-2 focus:ring-indigo-500 resize-none"></textarea>
      </div>
      <div>
        <label class="block text-sm font-medium text-gray-700 mb-1.5">Expires at *</label>
        <input type="datetime-local" name="expires_at" id="edit-ann-expires" required class="w-full px-4 py-3 border border-gray-200 rounded-xl text-sm focus:outline-none focus:ring-2 focus:ring-indigo-500" />
      </div>
      <div class="flex gap-3 pt-2">
        <button type="button" onclick="document.getElementById('edit-ann-modal').classList.add('hidden')" class="flex-1 px-4 py-3 border border-gray-200 rounded-xl text-sm font-medium text-gray-700 hover:bg-gray-50">Cancel</button>
        <button type="submit" class="flex-1 bg-indigo-600 hover:bg-indigo-700 text-white px-4 py-3 rounded-xl text-sm font-medium flex items-center justify-center gap-2"><i data-lucide="save" class="w-4 h-4"></i> Save</button>
      </div>
    </form>
  </div>
</div>

<?php require_once $_SERVER['DOCUMENT_ROOT'] . '/ByteClass/includes/scripts.php'; ?>
<script>
function editAnn(id, title, body, expires) {
  document.getElementById('edit-ann-id').value      = id;
  document.getElementById('edit-ann-title').value   = title;
  document.getElementById('edit-ann-body').value    = body;
  document.getElementById('edit-ann-expires').value = expires.replace(' ','T').substring(0,16);
  document.getElementById('edit-ann-modal').classList.remove('hidden');
  lucide.createIcons();
}
</script>
<?php require_once $_SERVER['DOCUMENT_ROOT'] . '/ByteClass/includes/footer-minimal.php'; ?>