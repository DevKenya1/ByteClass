<?php
$required_role = 'student';
require_once $_SERVER['DOCUMENT_ROOT'] . '/ByteClass/includes/auth-check.php';
require_once $_SERVER['DOCUMENT_ROOT'] . '/ByteClass/config/constants.php';
require_once $_SERVER['DOCUMENT_ROOT'] . '/ByteClass/config/database.php';
require_once $_SERVER['DOCUMENT_ROOT'] . '/ByteClass/helpers/response.php';
require_once $_SERVER['DOCUMENT_ROOT'] . '/ByteClass/helpers/points.php';

$db        = Database::getInstance()->getConnection();
$student_id = (int)$_SESSION['user_id'];
$course_id  = (int)($_GET['course_id']  ?? 0);
$lesson_id  = (int)($_GET['lesson_id']  ?? 0);

if (!$course_id) { header('Location: '.APP_URL.'/student/courses.php'); exit; }

// Verify student is enrolled
$enroll_check = $db->prepare("SELECT id FROM enrollments WHERE student_id=? AND course_id=?");
$enroll_check->execute([$student_id, $course_id]);
if (!$enroll_check->fetch()) { header('Location: '.APP_URL.'/student/explore.php'); exit; }

// Get course
$course = $db->prepare("SELECT * FROM courses WHERE id=?");
$course->execute([$course_id]); $course = $course->fetch();
if (!$course) { header('Location: '.APP_URL.'/student/courses.php'); exit; }

// Get all modules + lessons for this course
$all_modules = $db->prepare("
    SELECT m.*, 
        (SELECT COUNT(*) FROM lessons l WHERE l.module_id=m.id AND l.status='published') AS lesson_count
    FROM modules m WHERE m.course_id=? ORDER BY m.sort_order ASC, m.id ASC
"); $all_modules->execute([$course_id]); $all_modules = $all_modules->fetchAll();

$course_outline = [];
foreach ($all_modules as $mod) {
    $lessons_stmt = $db->prepare("
        SELECT l.*,
            (SELECT 1 FROM lesson_progress lp WHERE lp.lesson_id=l.id AND lp.student_id=? AND lp.is_complete=1) AS completed
        FROM lessons l WHERE l.module_id=? AND l.status='published'
        ORDER BY l.sort_order ASC, l.id ASC
    "); $lessons_stmt->execute([$student_id, $mod['id']]);
    $mod['lessons'] = $lessons_stmt->fetchAll();
    $course_outline[] = $mod;
}

// Collect all lesson IDs in order
$all_lesson_ids = [];
foreach ($course_outline as $mod) {
    foreach ($mod['lessons'] as $les) {
        $all_lesson_ids[] = (int)$les['id'];
    }
}

// If no lesson selected, pick first
if (!$lesson_id && !empty($all_lesson_ids)) {
    // Try to find the last incomplete lesson first
    foreach ($all_lesson_ids as $lid) {
        $comp = $db->prepare("SELECT 1 FROM lesson_progress WHERE lesson_id=? AND student_id=? AND is_complete=1");
        $comp->execute([$lid, $student_id]);
        if (!$comp->fetch()) { $lesson_id = $lid; break; }
    }
    if (!$lesson_id) $lesson_id = $all_lesson_ids[0];
}

// Get current lesson
$current_lesson = null;
if ($lesson_id) {
    $les_stmt = $db->prepare("SELECT * FROM lessons WHERE id=? AND status='published'");
    $les_stmt->execute([$lesson_id]); $current_lesson = $les_stmt->fetch();
}

// Handle lesson completion mark
if ($_SERVER['REQUEST_METHOD'] === 'POST' && ($_POST['action'] ?? '') === 'mark_complete') {
    $lid = (int)($_POST['lesson_id'] ?? 0);
    if ($lid) {
        // Upsert lesson progress
        $exists = $db->prepare("SELECT id FROM lesson_progress WHERE lesson_id=? AND student_id=?");
        $exists->execute([$lid, $student_id]);
        if ($exists->fetch()) {
            $db->prepare("UPDATE lesson_progress SET is_complete=1, completed_at=NOW() WHERE lesson_id=? AND student_id=?")
               ->execute([$lid, $student_id]);
        } else {
            $db->prepare("INSERT INTO lesson_progress (lesson_id, student_id, is_complete, completed_at) VALUES (?,?,1,NOW())")
               ->execute([$lid, $student_id]);
        }
        // Award lesson points (once only)
        $pts_check = $db->prepare("SELECT 1 FROM activity_logs WHERE user_id=? AND action='lesson_complete' AND description LIKE ? LIMIT 1");
        $pts_check->execute([$student_id, "%lesson:$lid%"]);
        if (!$pts_check->fetch()) {
            award_points($student_id, 100, "Lesson complete — lesson:$lid");
            $db->prepare("INSERT INTO activity_logs (user_id,action,description,ip_address) VALUES (?,?,?,?)")
               ->execute([$student_id,'lesson_complete',"Completed lesson:$lid",$_SERVER['REMOTE_ADDR']??'']);
        }

        // Check if module is complete
        $les_module = $db->prepare("SELECT module_id FROM lessons WHERE id=?");
        $les_module->execute([$lid]); $mod_id = (int)$les_module->fetchColumn();
        $total_in_mod  = (int)$db->query("SELECT COUNT(*) FROM lessons WHERE module_id=$mod_id AND status='published'")->fetchColumn();
        $done_in_mod   = (int)$db->prepare("SELECT COUNT(*) FROM lesson_progress lp JOIN lessons l ON l.id=lp.lesson_id WHERE l.module_id=? AND lp.student_id=? AND lp.is_complete=1")->execute([$mod_id,$student_id]) ? (int)$db->query("SELECT COUNT(*) FROM lesson_progress lp JOIN lessons l ON l.id=lp.lesson_id WHERE l.module_id=$mod_id AND lp.student_id=$student_id AND lp.is_complete=1")->fetchColumn() : 0;
        if ($total_in_mod > 0 && $done_in_mod >= $total_in_mod) {
            $mod_pts = $db->prepare("SELECT 1 FROM activity_logs WHERE user_id=? AND action='module_complete' AND description LIKE ? LIMIT 1");
            $mod_pts->execute([$student_id, "%module:$mod_id%"]);
            if (!$mod_pts->fetch()) {
                award_points($student_id, 500, "Module complete — module:$mod_id");
                $db->prepare("INSERT INTO activity_logs (user_id,action,description,ip_address) VALUES (?,?,?,?)")
                   ->execute([$student_id,'module_complete',"Completed module:$mod_id",$_SERVER['REMOTE_ADDR']??'']);
            }
        }

        // Check if entire course is complete
        $total_course  = (int)$db->query("SELECT COUNT(*) FROM lessons l JOIN modules m ON l.module_id=m.id WHERE m.course_id=$course_id AND l.status='published'")->fetchColumn();
        $done_course   = (int)$db->query("SELECT COUNT(*) FROM lesson_progress lp JOIN lessons l ON l.id=lp.lesson_id JOIN modules m ON l.module_id=m.id WHERE m.course_id=$course_id AND lp.student_id=$student_id AND lp.is_complete=1")->fetchColumn();
        if ($total_course > 0 && $done_course >= $total_course) {
            $db->prepare("UPDATE enrollments SET completed_at=NOW() WHERE student_id=? AND course_id=? AND completed_at IS NULL")
               ->execute([$student_id, $course_id]);
            $crs_pts = $db->prepare("SELECT 1 FROM activity_logs WHERE user_id=? AND action='course_complete' AND description LIKE ? LIMIT 1");
            $crs_pts->execute([$student_id, "%course:$course_id%"]);
            if (!$crs_pts->fetch()) {
                award_points($student_id, 1000, "Course complete — course:$course_id");
                $db->prepare("INSERT INTO activity_logs (user_id,action,description,ip_address) VALUES (?,?,?,?)")
                   ->execute([$student_id,'course_complete',"Completed course:$course_id",$_SERVER['REMOTE_ADDR']??'']);
            }
        }

        // Navigate to next lesson
        $cur_idx = array_search($lid, $all_lesson_ids);
        $next_id = isset($all_lesson_ids[$cur_idx+1]) ? $all_lesson_ids[$cur_idx+1] : null;
        $redirect = APP_URL.'/student/lesson-player.php?course_id='.$course_id;
        if ($next_id) $redirect .= '&lesson_id='.$next_id;
        header('Location: '.$redirect.'&completed=1'); exit;
    }
}

// Get completed lessons for this student
$completed_ids = [];
$comp_stmt = $db->prepare("SELECT lesson_id FROM lesson_progress WHERE student_id=? AND is_complete=1");
$comp_stmt->execute([$student_id]); 
foreach ($comp_stmt->fetchAll() as $r) $completed_ids[] = (int)$r['lesson_id'];

// Progress
$total_lessons  = count($all_lesson_ids);
$done_lessons   = count(array_intersect($all_lesson_ids, $completed_ids));
$progress_pct   = $total_lessons > 0 ? round(($done_lessons/$total_lessons)*100) : 0;

// Prev/Next lesson
$cur_idx  = $lesson_id ? array_search($lesson_id, $all_lesson_ids) : 0;
$prev_lid = ($cur_idx > 0) ? $all_lesson_ids[$cur_idx-1] : null;
$next_lid = ($cur_idx < count($all_lesson_ids)-1) ? $all_lesson_ids[$cur_idx+1] : null;

$page_title = ($current_lesson ? $current_lesson['title'].' — ' : '') . $course['name'];
require_once $_SERVER['DOCUMENT_ROOT'] . '/ByteClass/includes/head.php';
?>
<!DOCTYPE html>
<html>
<head>
<style>
/* Lesson content typography */
.lesson-content h1,.lesson-content h2,.lesson-content h3{font-weight:700;margin:1.2em 0 .6em;color:#111827}
.lesson-content h1{font-size:1.4em}.lesson-content h2{font-size:1.2em}.lesson-content h3{font-size:1.05em}
.lesson-content p{margin:.7em 0;line-height:1.75;color:#374151}
.lesson-content ul,.lesson-content ol{margin:.7em 0;padding-left:1.6em;color:#374151}
.lesson-content li{margin:.3em 0;line-height:1.7}
.lesson-content code{background:#f3f4f6;padding:.15em .4em;border-radius:4px;font-family:monospace;font-size:.88em;color:#4F46E5}
.lesson-content pre{background:#1e293b;color:#e2e8f0;padding:1.2em;border-radius:.75rem;overflow-x:auto;margin:1em 0;font-size:.85em;line-height:1.6}
.lesson-content strong{font-weight:700;color:#111827}
.lesson-content blockquote{border-left:3px solid #4F46E5;padding-left:1em;margin:1em 0;color:#6b7280;font-style:italic}
</style>
</head>

<div class="flex min-h-screen bg-gray-900">

  <!-- LEFT: Course outline sidebar -->
  <div id="outline-sidebar"
    class="w-80 bg-gray-900 border-r border-gray-800 flex flex-col flex-shrink-0 fixed left-0 top-0 h-full z-40 transition-all duration-300">

    <!-- Course header -->
    <div class="p-4 border-b border-gray-800">
      <a href="<?= APP_URL ?>/student/courses.php"
         class="flex items-center gap-2 text-gray-400 hover:text-white text-xs mb-3 transition-colors">
        <i data-lucide="arrow-left" class="w-3.5 h-3.5"></i> Back to My Courses
      </a>
      <h2 class="text-white font-bold text-sm leading-snug line-clamp-2"><?= htmlspecialchars($course['name']) ?></h2>
      <div class="mt-2">
        <div class="flex items-center justify-between text-xs text-gray-400 mb-1">
          <span><?= $done_lessons ?>/<?= $total_lessons ?> lessons</span>
          <span><?= $progress_pct ?>%</span>
        </div>
        <div class="w-full bg-gray-800 rounded-full h-1.5">
          <div class="bg-indigo-500 h-1.5 rounded-full transition-all" style="width:<?= $progress_pct ?>%"></div>
        </div>
      </div>
    </div>

    <!-- Outline list -->
    <div class="flex-1 overflow-y-auto py-2">
      <?php foreach ($course_outline as $modIdx => $mod): ?>
      <div class="mb-1">
        <div class="px-4 py-2.5 flex items-center gap-2">
          <span class="w-5 h-5 bg-gray-700 rounded text-gray-400 text-xs font-bold flex items-center justify-center flex-shrink-0">
            <?= $modIdx+1 ?>
          </span>
          <span class="text-gray-300 text-xs font-semibold uppercase tracking-wide"><?= htmlspecialchars($mod['title']) ?></span>
        </div>
        <?php foreach ($mod['lessons'] as $lesIdx => $les):
          $is_current  = (int)$les['id'] === $lesson_id;
          $is_complete = in_array((int)$les['id'], $completed_ids);
        ?>
        <a href="?course_id=<?= $course_id ?>&lesson_id=<?= $les['id'] ?>"
           class="flex items-center gap-3 px-4 py-2.5 transition-colors group
           <?= $is_current ? 'bg-indigo-600/20 border-r-2 border-indigo-500' : 'hover:bg-gray-800' ?>">
          <div class="w-5 h-5 rounded-full flex items-center justify-center flex-shrink-0
            <?= $is_complete ? 'bg-green-500' : ($is_current ? 'bg-indigo-500' : 'bg-gray-700') ?>">
            <?php if ($is_complete): ?>
            <i data-lucide="check" class="w-3 h-3 text-white"></i>
            <?php elseif ($les['video_url']): ?>
            <i data-lucide="play" class="w-2.5 h-2.5 <?= $is_current ? 'text-white' : 'text-gray-400' ?>"></i>
            <?php else: ?>
            <i data-lucide="file-text" class="w-2.5 h-2.5 <?= $is_current ? 'text-white' : 'text-gray-400' ?>"></i>
            <?php endif; ?>
          </div>
          <div class="flex-1 min-w-0">
            <p class="text-xs <?= $is_current ? 'text-white font-semibold' : 'text-gray-400 group-hover:text-gray-200' ?> truncate leading-snug">
              <?= htmlspecialchars($les['title']) ?>
            </p>
            <?php if ($les['duration_min']): ?>
            <p class="text-xs text-gray-600 mt-0.5"><?= $les['duration_min'] ?> min</p>
            <?php endif; ?>
          </div>
        </a>
        <?php endforeach; ?>
      </div>
      <?php endforeach; ?>
    </div>
  </div>

  <!-- RIGHT: Lesson content -->
  <div class="flex-1 flex flex-col" style="margin-left:320px;">

    <!-- Top navbar -->
    <header class="bg-gray-900 border-b border-gray-800 px-6 py-3 flex items-center justify-between sticky top-0 z-30">
      <div class="flex items-center gap-3">
        <button onclick="toggleOutline()"
          class="p-2 text-gray-400 hover:text-white hover:bg-gray-800 rounded-lg transition-colors">
          <i data-lucide="panel-left" class="w-5 h-5"></i>
        </button>
        <?php if ($current_lesson): ?>
        <div class="min-w-0">
          <p class="text-white text-sm font-semibold truncate max-w-md"><?= htmlspecialchars($current_lesson['title']) ?></p>
          <p class="text-gray-500 text-xs"><?= htmlspecialchars($course['name']) ?></p>
        </div>
        <?php endif; ?>
      </div>
      <div class="flex items-center gap-3">
        <span class="text-xs text-gray-500"><?= $progress_pct ?>% complete</span>
        <a href="<?= APP_URL ?>/student/dashboard.php"
           class="text-xs text-gray-400 hover:text-white bg-gray-800 px-3 py-1.5 rounded-lg transition-colors flex items-center gap-1.5">
          <i data-lucide="layout-dashboard" class="w-3.5 h-3.5"></i> Dashboard
        </a>
      </div>
    </header>

    <!-- Lesson main content -->
    <?php if (!$current_lesson): ?>
    <div class="flex-1 flex items-center justify-center">
      <div class="text-center">
        <i data-lucide="book-open" class="w-16 h-16 text-gray-600 mx-auto mb-4"></i>
        <h2 class="text-white text-xl font-bold mb-2">Welcome to <?= htmlspecialchars($course['name']) ?></h2>
        <p class="text-gray-400 mb-6">This course has no published lessons yet. Check back soon!</p>
        <a href="<?= APP_URL ?>/student/courses.php"
           class="inline-flex items-center gap-2 bg-indigo-600 text-white px-6 py-3 rounded-xl font-medium hover:bg-indigo-700 transition-colors">
          <i data-lucide="arrow-left" class="w-4 h-4"></i> Back to Courses
        </a>
      </div>
    </div>

    <?php else: ?>
    <div class="flex-1 overflow-y-auto">

      <!-- Success banner -->
      <?php if (isset($_GET['completed'])): ?>
      <div class="bg-green-600 text-white px-6 py-3 flex items-center gap-3 text-sm">
        <i data-lucide="check-circle" class="w-5 h-5 flex-shrink-0"></i>
        <p><strong>Lesson completed!</strong> You earned <strong>+100 points</strong>. Keep going!</p>
        <button onclick="this.parentElement.remove()" class="ml-auto text-green-200 hover:text-white"><i data-lucide="x" class="w-4 h-4"></i></button>
      </div>
      <?php endif; ?>

      <!-- Video section -->
      <?php if ($current_lesson['video_url']): ?>
      <div class="bg-black w-full" style="padding-top:56.25%;position:relative;">
        <iframe
          src="<?= htmlspecialchars($current_lesson['video_url']) ?>"
          style="position:absolute;top:0;left:0;width:100%;height:100%;"
          frameborder="0"
          allow="accelerometer; autoplay; clipboard-write; encrypted-media; gyroscope; picture-in-picture"
          allowfullscreen>
        </iframe>
      </div>
      <?php endif; ?>

      <!-- Lesson content area -->
      <div class="max-w-4xl mx-auto px-6 py-8">

        <!-- Lesson title + meta -->
        <div class="mb-6">
          <div class="flex items-center gap-2 text-xs text-indigo-400 font-semibold uppercase tracking-wide mb-2">
            <i data-lucide="book-open" class="w-3.5 h-3.5"></i>
            Lesson <?= ($cur_idx ?? 0) + 1 ?> of <?= $total_lessons ?>
            <?php if ($current_lesson['duration_min']): ?>
            · <i data-lucide="clock" class="w-3.5 h-3.5"></i> <?= $current_lesson['duration_min'] ?> min
            <?php endif; ?>
          </div>
          <h1 class="text-white text-2xl md:text-3xl font-black leading-tight">
            <?= htmlspecialchars($current_lesson['title']) ?>
          </h1>
          <?php if (in_array($lesson_id, $completed_ids)): ?>
          <span class="inline-flex items-center gap-1.5 bg-green-500/20 text-green-400 text-xs font-semibold px-3 py-1.5 rounded-full mt-3">
            <i data-lucide="check-circle" class="w-3.5 h-3.5"></i> Completed
          </span>
          <?php endif; ?>
        </div>

        <!-- Written content -->
        <?php if ($current_lesson['content']): ?>
        <div class="bg-gray-800/50 rounded-2xl p-6 mb-6 border border-gray-700">
          <div class="lesson-content text-gray-300">
            <?= nl2br(htmlspecialchars($current_lesson['content'])) ?>
          </div>
        </div>
        <?php endif; ?>

        <!-- Navigation + completion -->
        <div class="flex items-center gap-4 pt-4 border-t border-gray-800">
          <?php if ($prev_lid): ?>
          <a href="?course_id=<?= $course_id ?>&lesson_id=<?= $prev_lid ?>"
             class="flex items-center gap-2 px-4 py-3 bg-gray-800 text-gray-300 rounded-xl text-sm font-medium hover:bg-gray-700 transition-colors">
            <i data-lucide="arrow-left" class="w-4 h-4"></i> Previous
          </a>
          <?php endif; ?>

          <div class="flex-1"></div>

          <!-- 🆕 Take Quiz button (added before mark‑complete form) -->
          <?php
          $lesson_quiz = $db->prepare("SELECT q.id FROM quizzes q WHERE q.lesson_id=? LIMIT 1");
          $lesson_quiz->execute([$lesson_id]); $lq = $lesson_quiz->fetch();
          if ($lq): ?>
          <a href="<?= APP_URL ?>/student/quiz.php?quiz_id=<?= $lq['id'] ?>&course_id=<?= $course_id ?>"
             class="flex items-center gap-2 px-5 py-3 bg-amber-500 hover:bg-amber-600 text-white rounded-xl text-sm font-bold transition-colors">
            <i data-lucide="help-circle" class="w-4 h-4"></i> Take Quiz
          </a>
          <?php endif; ?>

          <?php if (!in_array($lesson_id, $completed_ids)): ?>
          <form method="POST">
            <input type="hidden" name="action" value="mark_complete">
            <input type="hidden" name="lesson_id" value="<?= $lesson_id ?>">
            <button type="submit"
              class="flex items-center gap-2 px-6 py-3 bg-green-600 hover:bg-green-500 text-white rounded-xl text-sm font-bold transition-colors shadow-lg">
              <i data-lucide="check-circle" class="w-5 h-5"></i>
              Mark as Complete<?= $next_lid ? ' & Continue' : ' & Finish Course' ?>
            </button>
          </form>
          <?php elseif ($next_lid): ?>
          <a href="?course_id=<?= $course_id ?>&lesson_id=<?= $next_lid ?>"
             class="flex items-center gap-2 px-6 py-3 bg-indigo-600 hover:bg-indigo-500 text-white rounded-xl text-sm font-bold transition-colors shadow-lg">
            Next Lesson <i data-lucide="arrow-right" class="w-4 h-4"></i>
          </a>
          <?php else: ?>
          <!-- Course complete state -->
          <div class="text-center">
            <div class="inline-flex items-center gap-3 bg-green-500/20 text-green-400 px-6 py-3 rounded-xl border border-green-500/30">
              <i data-lucide="award" class="w-6 h-6"></i>
              <div class="text-left">
                <p class="font-bold text-sm">Course Complete! 🎉</p>
                <p class="text-xs text-green-500">Your certificate has been generated</p>
              </div>
            </div>
            <div class="flex gap-3 mt-3 justify-center">
              <a href="<?= APP_URL ?>/student/certificates.php"
                 class="flex items-center gap-2 px-5 py-2.5 bg-indigo-600 text-white rounded-xl text-sm font-semibold hover:bg-indigo-700 transition-colors">
                <i data-lucide="download" class="w-4 h-4"></i> Get Certificate
              </a>
              <a href="<?= APP_URL ?>/student/courses.php"
                 class="flex items-center gap-2 px-5 py-2.5 bg-gray-800 text-gray-300 rounded-xl text-sm font-semibold hover:bg-gray-700 transition-colors">
                My Courses
              </a>
            </div>
          </div>
          <?php endif; ?>
        </div>

      </div>
    </div>
    <?php endif; ?>
  </div>
</div>

<script>
function toggleOutline() {
  const sidebar = document.getElementById('outline-sidebar');
  const content = sidebar.nextElementSibling;
  const isOpen  = sidebar.style.transform !== 'translateX(-100%)';
  sidebar.style.transform = isOpen ? 'translateX(-100%)' : 'translateX(0)';
  content.style.marginLeft = isOpen ? '0' : '320px';
}
document.addEventListener('DOMContentLoaded', () => lucide.createIcons());
</script>
<?php require_once $_SERVER['DOCUMENT_ROOT'] . '/ByteClass/includes/footer-minimal.php'; ?>