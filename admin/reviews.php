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
    if ($action === 'add_review') {
        $lid      = (int)($_POST['lecturer_id'] ?? 0);
        $rating   = (int)($_POST['rating'] ?? 3);
        $strengths    = sanitize($_POST['strengths'] ?? '');
        $improvements = sanitize($_POST['improvements'] ?? '');
        $comment      = sanitize($_POST['comment'] ?? '');
        $period       = sanitize($_POST['review_period'] ?? '');

        $db->prepare("INSERT INTO lecturer_reviews (lecturer_id, reviewed_by, rating, strengths, improvements, comment, review_period) VALUES (?,?,?,?,?,?,?)")
           ->execute([$lid, $_SESSION['user_id'], $rating, $strengths, $improvements, $comment, $period]);

        $db->prepare("INSERT INTO notifications (user_id, title, message, type) VALUES (?,?,?,?)")
           ->execute([$lid, 'New Performance Review', 'A new performance review has been posted for you.', 'review']);

        $success_msg = 'Review submitted successfully.';
    }
}

$selected_lecturer = (int)($_GET['lecturer'] ?? 0);
$lecturers = $db->query("SELECT id, full_name FROM users WHERE role='lecturer' AND status='active' ORDER BY full_name")->fetchAll();

$reviews = [];
if ($selected_lecturer) {
    $stmt = $db->prepare("
        SELECT lr.*, u.full_name AS lecturer_name, rb.full_name AS reviewer_name
        FROM lecturer_reviews lr
        JOIN users u ON u.id = lr.lecturer_id
        JOIN users rb ON rb.id = lr.reviewed_by
        WHERE lr.lecturer_id = ?
        ORDER BY lr.created_at DESC
    ");
    $stmt->execute([$selected_lecturer]);
    $reviews = $stmt->fetchAll();
}

$page_title = 'Lecturer Reviews';
require_once $_SERVER['DOCUMENT_ROOT'] . '/ByteClass/includes/head.php';
?>
<div class="flex min-h-screen bg-gray-50">
  <?php require_once $_SERVER['DOCUMENT_ROOT'] . '/ByteClass/includes/sidebar-admin.php'; ?>
  <div id="main-content" class="flex-1 flex flex-col" style="margin-left:256px;">
    <?php require_once $_SERVER['DOCUMENT_ROOT'] . '/ByteClass/includes/navbar-admin.php'; ?>
    <main class="flex-1 p-6">
      <div class="bg-gradient-to-r from-indigo-600 to-cyan-500 rounded-2xl px-6 py-5 mb-6 flex items-center justify-between">
        <div>
          <h2 class="text-white text-xl font-bold">Lecturer Performance Reviews</h2>
          <p class="text-indigo-100 text-sm mt-0.5">Track lecturer performance and improvement over time</p>
        </div>
        <button onclick="document.getElementById('add-review-modal').classList.remove('hidden')"
          class="bg-white text-indigo-600 px-4 py-2 rounded-xl text-sm font-semibold hover:bg-indigo-50 flex items-center gap-2">
          <i data-lucide="plus" class="w-4 h-4"></i> Add Review
        </button>
      </div>

      <?php if ($success_msg): ?><div class="mb-4 p-4 bg-green-50 border border-green-200 rounded-xl text-sm text-green-700 flex gap-3"><i data-lucide="check-circle" class="w-5 h-5 flex-shrink-0"></i><?= htmlspecialchars($success_msg) ?></div><?php endif; ?>

      <!-- Filter by lecturer -->
      <form method="GET" class="bg-white rounded-2xl border border-gray-100 p-4 mb-6 flex gap-3">
        <select name="lecturer" class="flex-1 px-4 py-2.5 border border-gray-200 rounded-xl text-sm focus:outline-none focus:ring-2 focus:ring-indigo-500">
          <option value="">Select lecturer to view reviews</option>
          <?php foreach ($lecturers as $l): ?>
          <option value="<?= $l['id'] ?>" <?= $selected_lecturer===$l['id']?'selected':'' ?>><?= htmlspecialchars($l['full_name']) ?></option>
          <?php endforeach; ?>
        </select>
        <button type="submit" class="bg-indigo-600 text-white px-4 py-2.5 rounded-xl text-sm font-medium hover:bg-indigo-700 flex items-center gap-2"><i data-lucide="eye" class="w-4 h-4"></i> View Reviews</button>
      </form>

      <?php if ($selected_lecturer): ?>
      <div class="space-y-4">
        <?php if (empty($reviews)): ?>
        <div class="bg-white rounded-2xl border border-gray-100 p-10 text-center text-gray-400">No reviews yet for this lecturer</div>
        <?php else: foreach ($reviews as $r): ?>
        <div class="bg-white rounded-2xl border border-gray-100 p-5">
          <div class="flex items-start justify-between mb-3">
            <div>
              <div class="flex items-center gap-2 mb-1">
                <div class="flex">
                  <?php for ($i=1; $i<=5; $i++): ?>
                  <i data-lucide="star" class="w-4 h-4 <?= $i <= $r['rating'] ? 'text-amber-400 fill-amber-400' : 'text-gray-300' ?>"></i>
                  <?php endfor; ?>
                </div>
                <span class="text-sm font-semibold text-gray-700"><?= $r['rating'] ?>/5</span>
                <?php if ($r['review_period']): ?><span class="text-xs bg-gray-100 text-gray-600 px-2 py-0.5 rounded-full"><?= htmlspecialchars($r['review_period']) ?></span><?php endif; ?>
              </div>
              <p class="text-xs text-gray-400">By <?= htmlspecialchars($r['reviewer_name']) ?> · <?= date('M d, Y', strtotime($r['created_at'])) ?></p>
            </div>
          </div>
          <?php if ($r['strengths']): ?>
          <div class="mb-2">
            <p class="text-xs font-semibold text-green-600 mb-1">Strengths</p>
            <p class="text-sm text-gray-700"><?= htmlspecialchars($r['strengths']) ?></p>
          </div>
          <?php endif; ?>
          <?php if ($r['improvements']): ?>
          <div class="mb-2">
            <p class="text-xs font-semibold text-amber-600 mb-1">Areas for improvement</p>
            <p class="text-sm text-gray-700"><?= htmlspecialchars($r['improvements']) ?></p>
          </div>
          <?php endif; ?>
          <?php if ($r['comment']): ?>
          <p class="text-sm text-gray-600 mt-2 pt-2 border-t border-gray-100"><?= htmlspecialchars($r['comment']) ?></p>
          <?php endif; ?>
        </div>
        <?php endforeach; endif; ?>
      </div>
      <?php endif; ?>
    </main>
  </div>
</div>

<!-- ADD REVIEW MODAL -->
<div id="add-review-modal" class="hidden fixed inset-0 z-50 flex items-center justify-center p-4" style="background:rgba(0,0,0,0.5);">
  <div class="bg-white rounded-2xl w-full max-w-lg shadow-xl max-h-screen overflow-y-auto">
    <div class="flex items-center justify-between px-6 py-4 border-b border-gray-100 sticky top-0 bg-white">
      <h3 class="font-semibold text-gray-900">Add Performance Review</h3>
      <button onclick="document.getElementById('add-review-modal').classList.add('hidden')" class="text-gray-400 hover:text-gray-600"><i data-lucide="x" class="w-5 h-5"></i></button>
    </div>
    <form method="POST" class="p-6 space-y-4">
      <input type="hidden" name="action" value="add_review">
      <div>
        <label class="block text-sm font-medium text-gray-700 mb-1.5">Lecturer *</label>
        <select name="lecturer_id" required class="w-full px-4 py-3 border border-gray-200 rounded-xl text-sm focus:outline-none focus:ring-2 focus:ring-indigo-500">
          <option value="">Select lecturer</option>
          <?php foreach ($lecturers as $l): ?><option value="<?= $l['id'] ?>"><?= htmlspecialchars($l['full_name']) ?></option><?php endforeach; ?>
        </select>
      </div>
      <div class="grid grid-cols-2 gap-4">
        <div>
          <label class="block text-sm font-medium text-gray-700 mb-1.5">Rating *</label>
          <select name="rating" required class="w-full px-4 py-3 border border-gray-200 rounded-xl text-sm focus:outline-none focus:ring-2 focus:ring-indigo-500">
            <?php for ($i=5; $i>=1; $i--): ?><option value="<?= $i ?>"><?= $i ?> Star<?= $i>1?'s':'' ?></option><?php endfor; ?>
          </select>
        </div>
        <div>
          <label class="block text-sm font-medium text-gray-700 mb-1.5">Review period</label>
          <input type="text" name="review_period" placeholder="e.g. Q1 2025" class="w-full px-4 py-3 border border-gray-200 rounded-xl text-sm focus:outline-none focus:ring-2 focus:ring-indigo-500" />
        </div>
      </div>
      <div>
        <label class="block text-sm font-medium text-gray-700 mb-1.5">Strengths</label>
        <textarea name="strengths" rows="2" class="w-full px-4 py-3 border border-gray-200 rounded-xl text-sm focus:outline-none focus:ring-2 focus:ring-indigo-500 resize-none"></textarea>
      </div>
      <div>
        <label class="block text-sm font-medium text-gray-700 mb-1.5">Areas for improvement</label>
        <textarea name="improvements" rows="2" class="w-full px-4 py-3 border border-gray-200 rounded-xl text-sm focus:outline-none focus:ring-2 focus:ring-indigo-500 resize-none"></textarea>
      </div>
      <div>
        <label class="block text-sm font-medium text-gray-700 mb-1.5">Overall comment</label>
        <textarea name="comment" rows="2" class="w-full px-4 py-3 border border-gray-200 rounded-xl text-sm focus:outline-none focus:ring-2 focus:ring-indigo-500 resize-none"></textarea>
      </div>
      <div class="flex gap-3 pt-2">
        <button type="button" onclick="document.getElementById('add-review-modal').classList.add('hidden')" class="flex-1 px-4 py-3 border border-gray-200 rounded-xl text-sm text-gray-700 hover:bg-gray-50">Cancel</button>
        <button type="submit" class="flex-1 bg-indigo-600 text-white px-4 py-3 rounded-xl text-sm font-medium hover:bg-indigo-700 flex items-center justify-center gap-2"><i data-lucide="send" class="w-4 h-4"></i> Submit Review</button>
      </div>
    </form>
  </div>
</div>

<?php require_once $_SERVER['DOCUMENT_ROOT'] . '/ByteClass/includes/scripts.php'; ?>
<?php require_once $_SERVER['DOCUMENT_ROOT'] . '/ByteClass/includes/footer-minimal.php'; ?>