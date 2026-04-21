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
    if ($action === 'create') {
        $course_id  = (int)($_POST['course_id'] ?? 0);
        $title      = sanitize($_POST['title']       ?? '');
        $scheduled  = sanitize($_POST['scheduled_at'] ?? '');
        $duration   = (int)($_POST['duration_min']   ?? 60);
        $platform   = sanitize($_POST['platform']    ?? 'zoom');
        $meet_link  = sanitize($_POST['meet_link']   ?? '');
        if (!$course_id||!$title||!$scheduled||!$meet_link) { $error_msg='All fields required.'; }
        else {
            $db->prepare("INSERT INTO class_sessions (course_id,lecturer_id,title,scheduled_at,duration_min,platform,meet_link,status) VALUES (?,?,?,?,?,?,?,'upcoming')")
               ->execute([$course_id,$id,$title,$scheduled,$duration,$platform,$meet_link]);
            // Notify enrolled students
            $enrolled = $db->prepare("SELECT student_id FROM enrollments WHERE course_id=?");
            $enrolled->execute([$course_id]);
            foreach ($enrolled->fetchAll() as $st) {
                $db->prepare("INSERT INTO notifications (user_id,title,message,type) VALUES (?,?,?,?)")
                   ->execute([$st['student_id'],'New Class Session Scheduled',"$title is scheduled. Check your sessions tab.",'course']);
            }
            $success_msg = 'Session scheduled and students notified.';
        }
    }
    if ($action === 'cancel') {
        $sid = (int)($_POST['session_id'] ?? 0);
        $db->prepare("UPDATE class_sessions SET status='cancelled' WHERE id=? AND lecturer_id=?")->execute([$sid,$id]);
        $success_msg = 'Session cancelled.';
    }
    if ($action === 'end') {
        $sid = (int)($_POST['session_id'] ?? 0);
        $db->prepare("UPDATE class_sessions SET status='ended' WHERE id=? AND lecturer_id=?")->execute([$sid,$id]);
        $success_msg = 'Session marked as ended.';
    }
}

$tab = sanitize($_GET['tab'] ?? 'upcoming');
$sessions_stmt = $db->prepare("
    SELECT cs.*, c.name AS course_name FROM class_sessions cs
    JOIN courses c ON cs.course_id=c.id
    JOIN course_lecturers cl ON cl.course_id=c.id
    WHERE cl.lecturer_id=? AND cs.status=?
    ORDER BY cs.scheduled_at " . ($tab==='upcoming'?'ASC':'DESC') . " LIMIT 50
");
$sessions_stmt->execute([$id, $tab==='upcoming'?'upcoming':'ended']);
$sessions = $sessions_stmt->fetchAll();

$my_courses = $db->prepare("SELECT c.id, c.name FROM courses c JOIN course_lecturers cl ON cl.course_id=c.id WHERE cl.lecturer_id=? ORDER BY c.name");
$my_courses->execute([$id]); $my_courses = $my_courses->fetchAll();

$page_title = 'Class Sessions';
require_once $_SERVER['DOCUMENT_ROOT'] . '/ByteClass/includes/head.php';
?>
<div class="flex min-h-screen bg-gray-50">
  <?php require_once $_SERVER['DOCUMENT_ROOT'] . '/ByteClass/includes/sidebar-lecturer.php'; ?>
  <div id="main-content" class="flex-1 flex flex-col" style="margin-left:256px;">
    <?php require_once $_SERVER['DOCUMENT_ROOT'] . '/ByteClass/includes/navbar-lecturer.php'; ?>
    <main class="flex-1 p-6">
      <div class="bg-gradient-to-r from-cyan-600 to-indigo-600 rounded-2xl px-6 py-5 mb-6 flex items-center justify-between">
        <div><h2 class="text-white text-xl font-bold">Class Sessions</h2><p class="text-cyan-100 text-sm mt-0.5">Schedule and manage live sessions</p></div>
        <button onclick="document.getElementById('schedule-modal').classList.remove('hidden')"
          class="bg-white text-indigo-600 px-4 py-2 rounded-xl text-sm font-semibold hover:bg-indigo-50 flex items-center gap-2">
          <i data-lucide="plus" class="w-4 h-4"></i> Schedule Session
        </button>
      </div>

      <?php if ($success_msg): ?><div class="mb-4 p-4 bg-green-50 border border-green-200 rounded-xl text-sm text-green-700 flex gap-3"><i data-lucide="check-circle" class="w-5 h-5 flex-shrink-0"></i><?= htmlspecialchars($success_msg) ?></div><?php endif; ?>
      <?php if ($error_msg): ?><div class="mb-4 p-4 bg-red-50 border border-red-200 rounded-xl text-sm text-red-700 flex gap-3"><i data-lucide="alert-circle" class="w-5 h-5 flex-shrink-0"></i><?= htmlspecialchars($error_msg) ?></div><?php endif; ?>

      <div class="flex gap-2 mb-6 border-b border-gray-200">
        <a href="?tab=upcoming" class="px-4 py-3 text-sm font-medium border-b-2 transition-colors <?= $tab==='upcoming' ? 'border-indigo-600 text-indigo-600' : 'border-transparent text-gray-500 hover:text-gray-700' ?>">Upcoming</a>
        <a href="?tab=ended"    class="px-4 py-3 text-sm font-medium border-b-2 transition-colors <?= $tab==='ended'    ? 'border-indigo-600 text-indigo-600' : 'border-transparent text-gray-500 hover:text-gray-700' ?>">Past</a>
      </div>

      <?php if (empty($sessions)): ?>
      <div class="bg-white rounded-2xl border border-gray-100 p-12 text-center">
        <i data-lucide="calendar" class="w-10 h-10 text-gray-300 mx-auto mb-3"></i>
        <p class="text-gray-400 text-sm"><?= $tab==='upcoming' ? 'No upcoming sessions. Schedule one now!' : 'No past sessions.' ?></p>
      </div>
      <?php else: ?>
      <div class="space-y-4">
        <?php foreach ($sessions as $s):
          $plat_c = match($s['platform']) { 'zoom'=>'bg-blue-100 text-blue-700','google_meet'=>'bg-green-100 text-green-700',default=>'bg-gray-100 text-gray-600'};
        ?>
        <div class="bg-white rounded-2xl border border-gray-100 p-5">
          <div class="flex items-start justify-between gap-4">
            <div class="flex items-start gap-4">
              <div class="w-12 h-12 bg-indigo-100 rounded-xl flex items-center justify-center flex-shrink-0">
                <i data-lucide="video" class="w-6 h-6 text-indigo-600"></i>
              </div>
              <div>
                <h3 class="font-semibold text-gray-900"><?= htmlspecialchars($s['title']) ?></h3>
                <p class="text-sm text-gray-500"><?= htmlspecialchars($s['course_name']) ?></p>
                <div class="flex items-center gap-3 mt-2 text-xs text-gray-400">
                  <span class="flex items-center gap-1"><i data-lucide="calendar" class="w-3 h-3"></i><?= date('M d, Y', strtotime($s['scheduled_at'])) ?></span>
                  <span class="flex items-center gap-1"><i data-lucide="clock" class="w-3 h-3"></i><?= date('H:i', strtotime($s['scheduled_at'])) ?></span>
                  <span class="flex items-center gap-1"><i data-lucide="timer" class="w-3 h-3"></i><?= $s['duration_min'] ?> min</span>
                  <span class="<?= $plat_c ?> px-2 py-0.5 rounded-full"><?= ucwords(str_replace('_',' ',$s['platform'])) ?></span>
                </div>
              </div>
            </div>
            <div class="flex items-center gap-2 flex-shrink-0">
              <?php if ($tab==='upcoming'): ?>
              <a href="<?= htmlspecialchars($s['meet_link']) ?>" target="_blank"
                 class="bg-indigo-600 text-white px-4 py-2 rounded-xl text-xs font-semibold hover:bg-indigo-700 transition-colors flex items-center gap-1.5">
                <i data-lucide="video" class="w-3.5 h-3.5"></i> Start Session
              </a>
              <form method="POST" class="inline">
                <input type="hidden" name="action" value="end">
                <input type="hidden" name="session_id" value="<?= $s['id'] ?>">
                <button type="submit" class="px-3 py-2 border border-gray-200 rounded-xl text-xs text-gray-600 hover:bg-gray-50 transition-colors">End</button>
              </form>
              <form method="POST" class="inline" onsubmit="return confirm('Cancel this session?')">
                <input type="hidden" name="action" value="cancel">
                <input type="hidden" name="session_id" value="<?= $s['id'] ?>">
                <button type="submit" class="px-3 py-2 bg-red-50 border border-red-200 rounded-xl text-xs text-red-600 hover:bg-red-100 transition-colors">Cancel</button>
              </form>
              <?php endif; ?>
            </div>
          </div>
        </div>
        <?php endforeach; ?>
      </div>
      <?php endif; ?>
    </main>
    <?php require_once $_SERVER['DOCUMENT_ROOT'] . '/ByteClass/includes/footer-minimal.php'; ?>
  </div>
</div>

<!-- Schedule Modal -->
<div id="schedule-modal" class="hidden fixed inset-0 z-50 flex items-center justify-center p-4" style="background:rgba(0,0,0,0.5);">
  <div class="bg-white rounded-2xl w-full max-w-lg shadow-xl">
    <div class="flex items-center justify-between px-6 py-4 border-b border-gray-100">
      <h3 class="font-semibold text-gray-900">Schedule New Session</h3>
      <button onclick="document.getElementById('schedule-modal').classList.add('hidden')" class="text-gray-400 hover:text-gray-600"><i data-lucide="x" class="w-5 h-5"></i></button>
    </div>
    <form method="POST" class="p-6 space-y-4">
      <input type="hidden" name="action" value="create">
      <div>
        <label class="block text-sm font-medium text-gray-700 mb-1.5">Course *</label>
        <select name="course_id" required class="w-full px-4 py-3 border border-gray-200 rounded-xl text-sm focus:outline-none focus:ring-2 focus:ring-indigo-500">
          <option value="">Select course</option>
          <?php foreach ($my_courses as $mc): ?><option value="<?= $mc['id'] ?>"><?= htmlspecialchars($mc['name']) ?></option><?php endforeach; ?>
        </select>
      </div>
      <div>
        <label class="block text-sm font-medium text-gray-700 mb-1.5">Session title *</label>
        <input type="text" name="title" required placeholder="e.g. Week 3 — Network Security Deep Dive"
          class="w-full px-4 py-3 border border-gray-200 rounded-xl text-sm focus:outline-none focus:ring-2 focus:ring-indigo-500" />
      </div>
      <div class="grid grid-cols-2 gap-4">
        <div>
          <label class="block text-sm font-medium text-gray-700 mb-1.5">Date & Time *</label>
          <input type="datetime-local" name="scheduled_at" required
            class="w-full px-4 py-3 border border-gray-200 rounded-xl text-sm focus:outline-none focus:ring-2 focus:ring-indigo-500" />
        </div>
        <div>
          <label class="block text-sm font-medium text-gray-700 mb-1.5">Duration (minutes)</label>
          <input type="number" name="duration_min" value="60" min="15" max="300"
            class="w-full px-4 py-3 border border-gray-200 rounded-xl text-sm focus:outline-none focus:ring-2 focus:ring-indigo-500" />
        </div>
        <div>
          <label class="block text-sm font-medium text-gray-700 mb-1.5">Platform *</label>
          <select name="platform" class="w-full px-4 py-3 border border-gray-200 rounded-xl text-sm focus:outline-none focus:ring-2 focus:ring-indigo-500">
            <option value="zoom">Zoom</option>
            <option value="google_meet">Google Meet</option>
            <option value="other">Other</option>
          </select>
        </div>
        <div>
          <label class="block text-sm font-medium text-gray-700 mb-1.5">Meeting link *</label>
          <input type="url" name="meet_link" required placeholder="https://zoom.us/j/..."
            class="w-full px-4 py-3 border border-gray-200 rounded-xl text-sm focus:outline-none focus:ring-2 focus:ring-indigo-500" />
        </div>
      </div>
      <div class="bg-indigo-50 border border-indigo-100 rounded-xl p-3 text-xs text-indigo-700 flex gap-2">
        <i data-lucide="info" class="w-4 h-4 flex-shrink-0"></i>
        All enrolled students will be notified when you schedule this session.
      </div>
      <div class="flex gap-3 pt-2">
        <button type="button" onclick="document.getElementById('schedule-modal').classList.add('hidden')" class="flex-1 px-4 py-3 border border-gray-200 rounded-xl text-sm text-gray-700 hover:bg-gray-50">Cancel</button>
        <button type="submit" class="flex-1 bg-indigo-600 text-white px-4 py-3 rounded-xl text-sm font-semibold hover:bg-indigo-700 flex items-center justify-center gap-2">
          <i data-lucide="calendar-plus" class="w-4 h-4"></i> Schedule
        </button>
      </div>
    </form>
  </div>
</div>
<?php require_once $_SERVER['DOCUMENT_ROOT'] . '/ByteClass/includes/scripts.php'; ?>
