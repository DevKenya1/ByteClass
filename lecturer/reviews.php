<?php
$required_role = 'lecturer';
require_once $_SERVER['DOCUMENT_ROOT'] . '/ByteClass/includes/auth-check.php';
require_once $_SERVER['DOCUMENT_ROOT'] . '/ByteClass/config/constants.php';
require_once $_SERVER['DOCUMENT_ROOT'] . '/ByteClass/config/database.php';
require_once $_SERVER['DOCUMENT_ROOT'] . '/ByteClass/helpers/response.php';
$db = Database::getInstance()->getConnection();
$id = (int)$_SESSION['user_id'];

$reviews = $db->prepare("
    SELECT lr.*, u.full_name AS reviewer_name FROM lecturer_reviews lr
    JOIN users u ON u.id=lr.reviewed_by
    WHERE lr.lecturer_id=? ORDER BY lr.created_at DESC
"); $reviews->execute([$id]); $reviews = $reviews->fetchAll();

$avg = $db->prepare("SELECT AVG(rating), COUNT(*) FROM lecturer_reviews WHERE lecturer_id=?");
$avg->execute([$id]); [$avg_rating, $total_reviews] = $avg->fetch(PDO::FETCH_NUM);
$avg_rating = round((float)$avg_rating, 1);

$page_title = 'Performance Reviews';
require_once $_SERVER['DOCUMENT_ROOT'] . '/ByteClass/includes/head.php';
?>
<div class="flex min-h-screen bg-gray-50">
  <?php require_once $_SERVER['DOCUMENT_ROOT'] . '/ByteClass/includes/sidebar-lecturer.php'; ?>
  <div id="main-content" class="flex-1 flex flex-col" style="margin-left:256px;">
    <?php require_once $_SERVER['DOCUMENT_ROOT'] . '/ByteClass/includes/navbar-lecturer.php'; ?>
    <main class="flex-1 p-6">
      <div class="bg-gradient-to-r from-cyan-600 to-indigo-600 rounded-2xl px-6 py-5 mb-6">
        <h2 class="text-white text-xl font-bold">Performance Reviews</h2>
        <p class="text-cyan-100 text-sm mt-0.5">Your teaching performance and student feedback</p>
      </div>

      <!-- Summary -->
      <div class="grid grid-cols-3 gap-4 mb-6">
        <div class="bg-white rounded-2xl border border-gray-100 p-5 text-center">
          <div class="flex items-center justify-center gap-1 mb-2">
            <?php for($i=1;$i<=5;$i++): ?>
            <i data-lucide="star" class="w-6 h-6 <?= $i<=round($avg_rating) ? 'text-amber-400 fill-amber-400' : 'text-gray-200' ?>"></i>
            <?php endfor; ?>
          </div>
          <p class="text-3xl font-black text-gray-900"><?= $avg_rating > 0 ? $avg_rating : 'N/A' ?></p>
          <p class="text-sm text-gray-500">Average rating</p>
        </div>
        <div class="bg-white rounded-2xl border border-gray-100 p-5 text-center">
          <p class="text-3xl font-black text-gray-900"><?= (int)$total_reviews ?></p>
          <p class="text-sm text-gray-500">Total reviews</p>
        </div>
        <div class="bg-white rounded-2xl border border-gray-100 p-5 text-center">
          <?php
          $five_stars = $db->prepare("SELECT COUNT(*) FROM lecturer_reviews WHERE lecturer_id=? AND rating=5"); $five_stars->execute([$id]);
          $pct = (int)$total_reviews > 0 ? round(($five_stars->fetchColumn()/$total_reviews)*100) : 0;
          ?>
          <p class="text-3xl font-black text-gray-900"><?= $pct ?>%</p>
          <p class="text-sm text-gray-500">5-star reviews</p>
        </div>
      </div>

      <?php if (empty($reviews)): ?>
      <div class="bg-white rounded-2xl border border-gray-100 p-12 text-center">
        <i data-lucide="star" class="w-10 h-10 text-gray-300 mx-auto mb-3"></i>
        <p class="text-gray-500 text-sm">No reviews yet. Keep delivering great content!</p>
      </div>
      <?php else: ?>
      <div class="space-y-4">
        <?php foreach ($reviews as $r): ?>
        <div class="bg-white rounded-2xl border border-gray-100 p-5">
          <div class="flex items-start justify-between mb-3">
            <div>
              <div class="flex items-center gap-2 mb-1">
                <div class="flex">
                  <?php for($i=1;$i<=5;$i++): ?>
                  <i data-lucide="star" class="w-4 h-4 <?= $i<=$r['rating'] ? 'text-amber-400 fill-amber-400' : 'text-gray-200' ?>"></i>
                  <?php endfor; ?>
                </div>
                <span class="text-sm font-semibold text-gray-700"><?= $r['rating'] ?>/5</span>
                <?php if ($r['review_period']): ?>
                <span class="text-xs bg-indigo-100 text-indigo-700 px-2 py-0.5 rounded-full"><?= htmlspecialchars($r['review_period']) ?></span>
                <?php endif; ?>
              </div>
              <p class="text-xs text-gray-400">By <?= htmlspecialchars($r['reviewer_name']) ?> · <?= date('M d, Y', strtotime($r['created_at'])) ?></p>
            </div>
          </div>
          <?php if ($r['strengths']): ?>
          <div class="mb-3 p-3 bg-green-50 rounded-xl">
            <p class="text-xs font-semibold text-green-700 mb-1 flex items-center gap-1"><i data-lucide="thumbs-up" class="w-3.5 h-3.5"></i>Strengths</p>
            <p class="text-sm text-green-800"><?= htmlspecialchars($r['strengths']) ?></p>
          </div>
          <?php endif; ?>
          <?php if ($r['improvements']): ?>
          <div class="mb-3 p-3 bg-amber-50 rounded-xl">
            <p class="text-xs font-semibold text-amber-700 mb-1 flex items-center gap-1"><i data-lucide="trending-up" class="w-3.5 h-3.5"></i>Areas for improvement</p>
            <p class="text-sm text-amber-800"><?= htmlspecialchars($r['improvements']) ?></p>
          </div>
          <?php endif; ?>
          <?php if ($r['comment']): ?>
          <p class="text-sm text-gray-600 pt-2 border-t border-gray-100"><?= htmlspecialchars($r['comment']) ?></p>
          <?php endif; ?>
        </div>
        <?php endforeach; ?>
      </div>
      <?php endif; ?>
    </main>
    <?php require_once $_SERVER['DOCUMENT_ROOT'] . '/ByteClass/includes/footer-minimal.php'; ?>
  </div>
</div>
<?php require_once $_SERVER['DOCUMENT_ROOT'] . '/ByteClass/includes/scripts.php'; ?>
