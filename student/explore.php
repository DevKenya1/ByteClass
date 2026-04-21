<?php
$required_role = 'student';
require_once $_SERVER['DOCUMENT_ROOT'] . '/ByteClass/includes/auth-check.php';
require_once $_SERVER['DOCUMENT_ROOT'] . '/ByteClass/config/constants.php';
require_once $_SERVER['DOCUMENT_ROOT'] . '/ByteClass/config/database.php';
require_once $_SERVER['DOCUMENT_ROOT'] . '/ByteClass/helpers/response.php';

$db = Database::getInstance()->getConnection();
$id = (int)$_SESSION['user_id'];

$search   = sanitize($_GET['search']   ?? '');
$category = sanitize($_GET['category'] ?? '');
$diff     = sanitize($_GET['diff']     ?? '');
$page     = max(1,(int)($_GET['page']  ?? 1));
$limit    = 12;
$offset   = ($page-1)*$limit;

$where  = ["c.status = 'published'"];
$params = [];
if ($search)   { $where[] = 'c.name LIKE ?';     $params[] = "%$search%"; }
if ($category) { $where[] = 'c.category = ?';    $params[] = $category; }
if ($diff)     { $where[] = 'c.difficulty = ?';  $params[] = $diff; }
$wsql = implode(' AND ', $where);

$cnt = $db->prepare("SELECT COUNT(*) FROM courses c WHERE $wsql");
$cnt->execute($params);
$total       = (int)$cnt->fetchColumn();
$total_pages = max(1,ceil($total/$limit));

$stmt = $db->prepare("
    SELECT c.*, u.full_name AS lecturer_name,
        (SELECT COUNT(*) FROM enrollments e WHERE e.course_id=c.id) AS enrolled_count,
        (SELECT COUNT(*) FROM modules m WHERE m.course_id=c.id) AS module_count,
        (SELECT 1 FROM enrollments e2 WHERE e2.course_id=c.id AND e2.student_id=?) AS is_enrolled
    FROM courses c
    LEFT JOIN course_lecturers cl ON cl.course_id=c.id
    LEFT JOIN users u ON u.id=cl.lecturer_id
    WHERE $wsql
    GROUP BY c.id
    ORDER BY c.created_at DESC
    LIMIT $limit OFFSET $offset
");
$stmt->execute(array_merge([$id], $params));
$courses = $stmt->fetchAll();

$categories = $db->query("SELECT DISTINCT category FROM courses WHERE status='published' ORDER BY category")->fetchAll(PDO::FETCH_COLUMN);

$page_title = 'Explore Courses';
require_once $_SERVER['DOCUMENT_ROOT'] . '/ByteClass/includes/head.php';
?>
<div class="flex min-h-screen bg-gray-50">
  <?php require_once $_SERVER['DOCUMENT_ROOT'] . '/ByteClass/includes/sidebar-student.php'; ?>
  <div id="main-content" class="flex-1 flex flex-col" style="margin-left:256px;">
    <?php require_once $_SERVER['DOCUMENT_ROOT'] . '/ByteClass/includes/navbar-student.php'; ?>
    <main class="flex-1 p-6">
      <div class="bg-gradient-to-r from-indigo-600 to-cyan-500 rounded-2xl px-6 py-5 mb-6">
        <h2 class="text-white text-xl font-bold">Explore Courses</h2>
        <p class="text-indigo-100 text-sm mt-0.5">Discover and enroll in courses that match your goals</p>
      </div>

      <!-- Filters -->
      <form method="GET" class="bg-white rounded-2xl border border-gray-100 p-4 mb-6 flex flex-wrap gap-3">
        <div class="flex-1 min-w-48 relative">
          <i data-lucide="search" class="w-4 h-4 absolute left-3 top-1/2 -translate-y-1/2 text-gray-400"></i>
          <input type="text" name="search" value="<?= htmlspecialchars($search) ?>" placeholder="Search courses..."
            class="w-full pl-9 pr-4 py-2.5 border border-gray-200 rounded-xl text-sm focus:outline-none focus:ring-2 focus:ring-indigo-500" />
        </div>
        <select name="category" class="px-4 py-2.5 border border-gray-200 rounded-xl text-sm focus:outline-none focus:ring-2 focus:ring-indigo-500">
          <option value="">All categories</option>
          <?php foreach ($categories as $cat): ?>
          <option value="<?= $cat ?>" <?= $category===$cat?'selected':'' ?>><?= htmlspecialchars($cat) ?></option>
          <?php endforeach; ?>
        </select>
        <select name="diff" class="px-4 py-2.5 border border-gray-200 rounded-xl text-sm focus:outline-none focus:ring-2 focus:ring-indigo-500">
          <option value="">All levels</option>
          <option value="beginner"     <?= $diff==='beginner'?'selected':'' ?>>Beginner</option>
          <option value="intermediate" <?= $diff==='intermediate'?'selected':'' ?>>Intermediate</option>
          <option value="advanced"     <?= $diff==='advanced'?'selected':'' ?>>Advanced</option>
        </select>
        <button type="submit" class="bg-indigo-600 text-white px-4 py-2.5 rounded-xl text-sm font-medium hover:bg-indigo-700 flex items-center gap-2">
          <i data-lucide="filter" class="w-4 h-4"></i> Filter
        </button>
        <?php if ($search||$category||$diff): ?>
        <a href="<?= APP_URL ?>/student/explore.php" class="px-4 py-2.5 border border-gray-200 rounded-xl text-sm text-gray-600 hover:bg-gray-50">Clear</a>
        <?php endif; ?>
      </form>

      <?php if (empty($courses)): ?>
      <div class="bg-white rounded-2xl border border-gray-100 p-12 text-center">
        <i data-lucide="search" class="w-10 h-10 text-gray-300 mx-auto mb-3"></i>
        <p class="text-gray-500">No courses found matching your search</p>
      </div>
      <?php else: ?>
      <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 gap-5 mb-6">
        <?php foreach ($courses as $c):
          $dc = match($c['difficulty']) { 'beginner'=>'bg-green-500','intermediate'=>'bg-amber-500',default=>'bg-red-500' };
        ?>
        <div class="bg-white rounded-2xl border border-gray-100 overflow-hidden hover:shadow-md transition-shadow">
          <div class="relative h-40">
            <?php if ($c['thumbnail']): ?>
            <img src="<?= htmlspecialchars($c['thumbnail']) ?>" class="w-full h-full object-cover" />
            <?php else: ?>
            <div class="w-full h-full bg-gradient-to-br from-indigo-600 to-cyan-500 flex items-center justify-center">
              <i data-lucide="book-open" class="w-8 h-8 text-white opacity-60"></i>
            </div>
            <?php endif; ?>
            <span class="absolute top-2 left-2 text-xs font-semibold px-2.5 py-1 rounded-full text-white <?= $dc ?>">
              <?= ucfirst($c['difficulty']) ?>
            </span>
            <?php if ($c['is_enrolled']): ?>
            <span class="absolute top-2 right-2 bg-green-500 text-white text-xs px-2.5 py-1 rounded-full font-medium">Enrolled</span>
            <?php endif; ?>
          </div>
          <div class="p-4">
            <h3 class="font-semibold text-gray-900 mb-1 leading-tight"><?= htmlspecialchars($c['name']) ?></h3>
            <p class="text-xs text-gray-400 mb-2"><?= htmlspecialchars($c['category']) ?></p>
            <?php if ($c['lecturer_name']): ?>
            <p class="text-xs text-gray-500 mb-3">by <?= htmlspecialchars($c['lecturer_name']) ?></p>
            <?php endif; ?>
<?php $short = strlen($c['overview']) > 120; ?>
<p class="text-xs text-gray-600 mb-4 leading-relaxed" id="overview-<?= $c['id'] ?>">
  <?= htmlspecialchars($short ? substr($c['overview'],0,120) : $c['overview']) ?>
  <?php if ($short): ?>
  <span id="more-<?= $c['id'] ?>" class="hidden"><?= htmlspecialchars(substr($c['overview'],120)) ?></span>
  <button onclick="toggleOverview(<?= $c['id'] ?>)" id="btn-<?= $c['id'] ?>"
    class="text-indigo-600 hover:underline font-medium ml-1">...see more</button>
  <?php endif; ?>
</p>            <div class="flex items-center justify-between mb-4">
              <div>
                <p class="text-lg font-bold text-indigo-600">KES <?= number_format($c['price_kes'],2) ?></p>
                <p class="text-xs text-gray-400">USD <?= number_format($c['price_usd'],2) ?></p>
              </div>
              <div class="text-right">
                <p class="text-sm font-medium text-gray-700"><?= $c['enrolled_count'] ?> students</p>
                <p class="text-xs text-gray-400"><?= $c['module_count'] ?> modules</p>
              </div>
            </div>
            <?php if ($c['is_enrolled']): ?>
            <a href="<?= APP_URL ?>/student/courses.php"
               class="w-full bg-green-600 text-white py-2.5 rounded-xl text-sm font-medium hover:bg-green-700 transition-colors flex items-center justify-center gap-2">
              <i data-lucide="play" class="w-4 h-4"></i> Continue Learning
            </a>
            <?php elseif ($c['price_kes'] == 0): ?>
            <a href="<?= APP_URL ?>/student/enroll.php?course_id=<?= $c['id'] ?>"
               class="w-full bg-indigo-600 text-white py-2.5 rounded-xl text-sm font-medium hover:bg-indigo-700 transition-colors flex items-center justify-center gap-2">
              <i data-lucide="user-plus" class="w-4 h-4"></i> Enroll Free
            </a>
            <?php else: ?>
            <a href="<?= APP_URL ?>/student/enroll.php?course_id=<?= $c['id'] ?>"
               class="w-full bg-indigo-600 text-white py-2.5 rounded-xl text-sm font-medium hover:bg-indigo-700 transition-colors flex items-center justify-center gap-2">
              <i data-lucide="shopping-cart" class="w-4 h-4"></i> Enroll Now
            </a>
            <?php endif; ?>
          </div>
        </div>
        <?php endforeach; ?>
      </div>

      <?php if ($total_pages > 1): ?>
      <div class="flex justify-center gap-2">
        <?php if ($page>1): ?><a href="?page=<?= $page-1 ?>&search=<?= urlencode($search) ?>&category=<?= urlencode($category) ?>&diff=<?= urlencode($diff) ?>" class="px-4 py-2 border border-gray-200 rounded-xl text-sm hover:bg-gray-50">Previous</a><?php endif; ?>
        <span class="px-4 py-2 text-sm text-gray-500">Page <?= $page ?> of <?= $total_pages ?></span>
        <?php if ($page<$total_pages): ?><a href="?page=<?= $page+1 ?>&search=<?= urlencode($search) ?>&category=<?= urlencode($category) ?>&diff=<?= urlencode($diff) ?>" class="px-4 py-2 border border-gray-200 rounded-xl text-sm hover:bg-gray-50">Next</a><?php endif; ?>
      </div>
      <?php endif; ?>
      <?php endif; ?>
    </main>
    <?php require_once $_SERVER['DOCUMENT_ROOT'] . '/ByteClass/includes/footer-minimal.php'; ?>
  </div>
</div>
<script>
function toggleOverview(id) {
  const more = document.getElementById('more-' + id);
  const btn  = document.getElementById('btn-' + id);
  if (more.classList.contains('hidden')) {
    more.classList.remove('hidden');
    btn.textContent = 'see less';
  } else {
    more.classList.add('hidden');
    btn.textContent = '...see more';
  }
}
</script>
<?php require_once $_SERVER['DOCUMENT_ROOT'] . '/ByteClass/includes/scripts.php'; ?>
