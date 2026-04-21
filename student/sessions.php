<?php
$required_role = 'student';
require_once $_SERVER['DOCUMENT_ROOT'] . '/ByteClass/includes/auth-check.php';
require_once $_SERVER['DOCUMENT_ROOT'] . '/ByteClass/config/constants.php';
require_once $_SERVER['DOCUMENT_ROOT'] . '/ByteClass/config/database.php';
require_once $_SERVER['DOCUMENT_ROOT'] . '/ByteClass/helpers/response.php';

$db = Database::getInstance()->getConnection();
$id = (int)$_SESSION['user_id'];

$tab = sanitize($_GET['tab'] ?? 'upcoming');

$stmt = $db->prepare("
    SELECT cs.*, c.name AS course_name, u.full_name AS lecturer_name
    FROM class_sessions cs
    JOIN courses c ON cs.course_id=c.id
    JOIN enrollments e ON e.course_id=c.id AND e.student_id=?
    JOIN users u ON u.id=cs.lecturer_id
    WHERE cs.status = ?
    ORDER BY cs.scheduled_at " . ($tab==='upcoming' ? 'ASC' : 'DESC') . "
    LIMIT 30
");
$stmt->execute([$id, $tab==='upcoming' ? 'upcoming' : 'ended']);
$sessions = $stmt->fetchAll();

$page_title = 'Class Sessions';
require_once $_SERVER['DOCUMENT_ROOT'] . '/ByteClass/includes/head.php';
?>
<div class="flex min-h-screen bg-gray-50">
  <?php require_once $_SERVER['DOCUMENT_ROOT'] . '/ByteClass/includes/sidebar-student.php'; ?>
  <div id="main-content" class="flex-1 flex flex-col" style="margin-left:256px;">
    <?php require_once $_SERVER['DOCUMENT_ROOT'] . '/ByteClass/includes/navbar-student.php'; ?>
    <main class="flex-1 p-6">
      <div class="bg-gradient-to-r from-indigo-600 to-cyan-500 rounded-2xl px-6 py-5 mb-6">
        <h2 class="text-white text-xl font-bold">Class Sessions</h2>
        <p class="text-indigo-100 text-sm mt-0.5">Join live classes with your lecturers</p>
      </div>

      <div class="flex gap-2 mb-6 border-b border-gray-200">
        <a href="?tab=upcoming" class="px-4 py-3 text-sm font-medium border-b-2 transition-colors <?= $tab==='upcoming' ? 'border-indigo-600 text-indigo-600' : 'border-transparent text-gray-500 hover:text-gray-700' ?>">Upcoming</a>
        <a href="?tab=ended"    class="px-4 py-3 text-sm font-medium border-b-2 transition-colors <?= $tab==='ended'    ? 'border-indigo-600 text-indigo-600' : 'border-transparent text-gray-500 hover:text-gray-700' ?>">Past Sessions</a>
      </div>

      <?php if (empty($sessions)): ?>
      <div class="bg-white rounded-2xl border border-gray-100 p-12 text-center">
        <i data-lucide="calendar" class="w-10 h-10 text-gray-300 mx-auto mb-3"></i>
        <p class="text-gray-500"><?= $tab==='upcoming' ? 'No upcoming sessions' : 'No past sessions' ?></p>
      </div>
      <?php else: ?>
      <div class="space-y-4">
        <?php foreach ($sessions as $s):
          $is_live = $s['status'] === 'live';
          $plat_colors = ['zoom'=>'bg-blue-100 text-blue-700','google_meet'=>'bg-green-100 text-green-700','other'=>'bg-gray-100 text-gray-600'];
          $pc = $plat_colors[$s['platform']] ?? 'bg-gray-100 text-gray-600';
        ?>
        <div class="bg-white rounded-2xl border <?= $is_live ? 'border-red-300' : 'border-gray-100' ?> p-5">
          <div class="flex items-start justify-between gap-4">
            <div class="flex items-start gap-4">
              <div class="w-12 h-12 <?= $is_live ? 'bg-red-100' : 'bg-indigo-100' ?> rounded-xl flex items-center justify-center flex-shrink-0">
                <i data-lucide="video" class="w-6 h-6 <?= $is_live ? 'text-red-600' : 'text-indigo-600' ?>"></i>
              </div>
              <div>
                <div class="flex items-center gap-2 mb-1">
                  <h3 class="font-semibold text-gray-900"><?= htmlspecialchars($s['title']) ?></h3>
                  <?php if ($is_live): ?>
                  <span class="bg-red-500 text-white text-xs px-2 py-0.5 rounded-full animate-pulse font-medium">LIVE</span>
                  <?php endif; ?>
                </div>
                <p class="text-sm text-gray-500"><?= htmlspecialchars($s['course_name']) ?> · <?= htmlspecialchars($s['lecturer_name']) ?></p>
                <div class="flex items-center gap-3 mt-2 text-xs text-gray-400">
                  <span class="flex items-center gap-1"><i data-lucide="calendar" class="w-3 h-3"></i><?= date('M d, Y', strtotime($s['scheduled_at'])) ?></span>
                  <span class="flex items-center gap-1"><i data-lucide="clock" class="w-3 h-3"></i><?= date('H:i', strtotime($s['scheduled_at'])) ?></span>
                  <span class="flex items-center gap-1"><i data-lucide="timer" class="w-3 h-3"></i><?= $s['duration_min'] ?> min</span>
                  <span class="<?= $pc ?> px-2 py-0.5 rounded-full"><?= ucwords(str_replace('_',' ',$s['platform'])) ?></span>
                </div>
              </div>
            </div>
            <?php if ($tab === 'upcoming' || $is_live): ?>
            <a href="<?= htmlspecialchars($s['meet_link']) ?>" target="_blank"
               class="flex-shrink-0 <?= $is_live ? 'bg-red-600 hover:bg-red-700' : 'bg-indigo-600 hover:bg-indigo-700' ?> text-white px-5 py-2.5 rounded-xl text-sm font-medium transition-colors flex items-center gap-2">
              <i data-lucide="video" class="w-4 h-4"></i>
              <?= $is_live ? 'Join Now' : 'Join Session' ?>
            </a>
            <?php endif; ?>
          </div>
        </div>
        <?php endforeach; ?>
      </div>
      <?php endif; ?>
    </main>
    <?php require_once $_SERVER['DOCUMENT_ROOT'] . '/ByteClass/includes/footer-minimal.php'; ?>
  </div>
</div>
<?php require_once $_SERVER['DOCUMENT_ROOT'] . '/ByteClass/includes/scripts.php'; ?>
