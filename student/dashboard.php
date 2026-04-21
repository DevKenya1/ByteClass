<?php
$required_role = 'student';
require_once $_SERVER['DOCUMENT_ROOT'] . '/ByteClass/includes/auth-check.php';
require_once $_SERVER['DOCUMENT_ROOT'] . '/ByteClass/config/constants.php';
require_once $_SERVER['DOCUMENT_ROOT'] . '/ByteClass/config/database.php';
require_once $_SERVER['DOCUMENT_ROOT'] . '/ByteClass/helpers/response.php';

$db = Database::getInstance()->getConnection();
$id = (int)$_SESSION['user_id'];

// Stats
$enrolled  = (int)$db->prepare("SELECT COUNT(*) FROM enrollments WHERE student_id=?")->execute([$id]) ? $db->query("SELECT COUNT(*) FROM enrollments WHERE student_id=$id")->fetchColumn() : 0;
$s1 = $db->prepare("SELECT COUNT(*) FROM enrollments WHERE student_id=?"); $s1->execute([$id]); $enrolled = (int)$s1->fetchColumn();
$s2 = $db->prepare("SELECT COUNT(*) FROM enrollments WHERE student_id=? AND completed_at IS NOT NULL"); $s2->execute([$id]); $completed = (int)$s2->fetchColumn();
$s3 = $db->prepare("SELECT points FROM users WHERE id=?"); $s3->execute([$id]); $points = (int)$s3->fetchColumn();
$s4 = $db->prepare("SELECT COUNT(*)+1 FROM users WHERE role='student' AND points > (SELECT points FROM users WHERE id=?)"); $s4->execute([$id]); $rank = (int)$s4->fetchColumn();

// Enrolled courses with progress
$courses_stmt = $db->prepare("
    SELECT c.id, c.name, c.thumbnail, c.difficulty, e.enrolled_at, e.completed_at,
        u.full_name AS lecturer_name,
        (SELECT COUNT(*) FROM lessons l JOIN modules m ON l.module_id=m.id WHERE m.course_id=c.id AND l.status='published') AS total_lessons,
        (SELECT COUNT(*) FROM lesson_progress lp JOIN lessons l ON lp.lesson_id=l.id JOIN modules m ON l.module_id=m.id WHERE m.course_id=c.id AND lp.student_id=? AND lp.is_complete=1) AS done_lessons
    FROM enrollments e
    JOIN courses c ON e.course_id=c.id
    LEFT JOIN course_lecturers cl ON cl.course_id=c.id
    LEFT JOIN users u ON u.id=cl.lecturer_id
    WHERE e.student_id=?
    GROUP BY c.id, e.enrolled_at, e.completed_at
    ORDER BY e.enrolled_at DESC LIMIT 6
");
$courses_stmt->execute([$id, $id]);
$courses = $courses_stmt->fetchAll();
foreach ($courses as &$c) {
    $c['progress'] = $c['total_lessons'] > 0 ? round(($c['done_lessons']/$c['total_lessons'])*100) : 0;
}

// Leaderboard top 9
$lb = $db->query("SELECT full_name, profile_photo, points, RANK() OVER (ORDER BY points DESC) AS rnk FROM users WHERE role='student' AND status='active' ORDER BY points DESC LIMIT 9")->fetchAll();

// My rank info
$me_stmt = $db->prepare("SELECT full_name, profile_photo, points FROM users WHERE id=?");
$me_stmt->execute([$id]);
$me = $me_stmt->fetch();
$me['rank'] = $rank;

// Upcoming sessions
$sess_stmt = $db->prepare("
    SELECT cs.id, cs.title, cs.scheduled_at, cs.duration_min, cs.meet_link, cs.platform, c.name AS course_name
    FROM class_sessions cs
    JOIN courses c ON cs.course_id=c.id
    JOIN enrollments e ON e.course_id=c.id AND e.student_id=?
    WHERE cs.scheduled_at >= NOW() AND cs.status='upcoming'
    ORDER BY cs.scheduled_at ASC LIMIT 3
");
$sess_stmt->execute([$id]);
$sessions = $sess_stmt->fetchAll();

$page_title = 'Dashboard';
require_once $_SERVER['DOCUMENT_ROOT'] . '/ByteClass/includes/head.php';
?>
<div class="flex min-h-screen bg-gray-50">
  <?php require_once $_SERVER['DOCUMENT_ROOT'] . '/ByteClass/includes/sidebar-student.php'; ?>
  <div id="main-content" class="flex-1 flex flex-col" style="margin-left:256px;">
    <?php require_once $_SERVER['DOCUMENT_ROOT'] . '/ByteClass/includes/navbar-student.php'; ?>

    <main class="flex-1 p-6">
      <!-- Welcome -->
      <div class="bg-gradient-to-r from-indigo-600 to-cyan-500 rounded-2xl px-6 py-5 mb-6 flex items-center justify-between">
        <div>
          <h2 class="text-white text-xl font-bold">Welcome back, <?= htmlspecialchars(explode(' ',$_SESSION['full_name'])[0]) ?> 👋</h2>
          <p class="text-indigo-100 text-sm mt-0.5">Keep learning, keep growing!</p>
        </div>
        <?php if ($_SESSION['photo']): ?>
        <img src="<?= htmlspecialchars($_SESSION['photo']) ?>" class="w-14 h-14 rounded-2xl object-cover border-2 border-white border-opacity-30" />
        <?php else: ?>
        <div class="w-14 h-14 bg-white bg-opacity-20 rounded-2xl flex items-center justify-center text-white text-xl font-bold">
          <?= strtoupper(substr($_SESSION['full_name'],0,1)) ?>
        </div>
        <?php endif; ?>
      </div>

      <!-- Stats -->
      <div class="grid grid-cols-2 lg:grid-cols-4 gap-4 mb-6">
        <?php foreach ([
          ['label'=>'Enrolled courses',  'value'=>$enrolled,                         'icon'=>'book-open',    'color'=>'bg-indigo-500'],
          ['label'=>'Points earned',     'value'=>number_format($points),             'icon'=>'star',         'color'=>'bg-amber-500'],
          ['label'=>'Completed courses', 'value'=>$completed,                        'icon'=>'check-circle', 'color'=>'bg-green-500'],
          ['label'=>'Your rank',         'value'=>'#' . number_format($rank),        'icon'=>'trophy',       'color'=>'bg-cyan-500'],
        ] as $s): ?>
        <div class="bg-white rounded-2xl p-5 border border-gray-100 flex items-center gap-4 dash-card">
          <div class="w-12 h-12 <?= $s['color'] ?> rounded-xl flex items-center justify-center flex-shrink-0">
            <i data-lucide="<?= $s['icon'] ?>" class="w-6 h-6 text-white"></i>
          </div>
          <div>
            <p class="text-2xl font-bold text-gray-900"><?= $s['value'] ?></p>
            <p class="text-xs text-gray-500 mt-0.5"><?= $s['label'] ?></p>
          </div>
        </div>
        <?php endforeach; ?>
      </div>

      <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
        <!-- Courses -->
        <div class="lg:col-span-2">
          <div class="flex items-center justify-between mb-4">
            <h2 class="font-semibold text-gray-900">My courses</h2>
            <a href="<?= APP_URL ?>/student/courses.php" class="text-sm text-indigo-600 hover:underline">View all</a>
          </div>
          <?php if (empty($courses)): ?>
          <div class="bg-white rounded-2xl border border-gray-100 p-12 text-center">
            <i data-lucide="book-open" class="w-10 h-10 text-gray-300 mx-auto mb-3"></i>
            <p class="text-gray-500 font-medium">No courses enrolled yet</p>
            <p class="text-gray-400 text-sm mt-1 mb-4">Browse our catalog and start learning</p>
            <a href="<?= APP_URL ?>/student/explore.php"
               class="inline-block bg-indigo-600 text-white px-5 py-2.5 rounded-xl text-sm font-medium hover:bg-indigo-700">
              Explore courses
            </a>
          </div>
          <?php else: ?>
          <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
            <?php foreach ($courses as $c): ?>
            <div class="bg-white rounded-2xl border border-gray-100 overflow-hidden hover:shadow-md transition-shadow">
              <div class="relative h-36">
                <?php if ($c['thumbnail']): ?>
                <img src="<?= htmlspecialchars($c['thumbnail']) ?>" class="w-full h-full object-cover" />
                <?php else: ?>
                <div class="w-full h-full bg-gradient-to-br from-indigo-600 to-cyan-500 flex items-center justify-center">
                  <i data-lucide="book-open" class="w-8 h-8 text-white opacity-60"></i>
                </div>
                <?php endif; ?>
                <?php
                $dc = match($c['difficulty']) { 'beginner'=>'bg-green-500','intermediate'=>'bg-amber-500',default=>'bg-red-500' };
                ?>
                <span class="absolute top-2 left-2 text-xs font-semibold px-2 py-0.5 rounded-full text-white <?= $dc ?>">
                  <?= ucfirst($c['difficulty']) ?>
                </span>
                <span class="absolute top-2 right-2 bg-black bg-opacity-50 text-white text-xs px-2 py-0.5 rounded-full">
                  <?= $c['progress'] ?>%
                </span>
              </div>
              <div class="p-4">
                <h3 class="font-semibold text-gray-900 text-sm mb-1 truncate"><?= htmlspecialchars($c['name']) ?></h3>
                <?php if ($c['lecturer_name']): ?>
                <p class="text-xs text-gray-400 mb-3">by <?= htmlspecialchars($c['lecturer_name']) ?></p>
                <?php endif; ?>
                <div class="w-full bg-gray-100 rounded-full h-1.5 mb-3">
                  <div class="bg-indigo-500 h-1.5 rounded-full" style="width:<?= $c['progress'] ?>%"></div>
                </div>
                <div class="flex items-center justify-between">
                  <span class="text-xs text-gray-400"><?= $c['done_lessons'] ?>/<?= $c['total_lessons'] ?> lessons</span>
                  <a href="<?= APP_URL ?>/student/courses.php" class="text-xs text-indigo-600 font-medium hover:underline">Continue →</a>
                </div>
              </div>
            </div>
            <?php endforeach; ?>
          </div>
          <?php endif; ?>

          <?php if (!empty($sessions)): ?>
          <h2 class="font-semibold text-gray-900 mt-6 mb-4 flex items-center gap-2">
            <i data-lucide="calendar" class="w-4 h-4 text-indigo-600"></i> Upcoming sessions
          </h2>
          <div class="space-y-3">
            <?php foreach ($sessions as $s): ?>
            <div class="bg-white rounded-xl border border-gray-100 p-4 flex items-center gap-4">
              <div class="w-10 h-10 bg-cyan-100 rounded-xl flex items-center justify-center flex-shrink-0">
                <i data-lucide="calendar" class="w-5 h-5 text-cyan-600"></i>
              </div>
              <div class="flex-1">
                <p class="text-sm font-medium text-gray-900"><?= htmlspecialchars($s['title']) ?></p>
                <p class="text-xs text-gray-400"><?= htmlspecialchars($s['course_name']) ?> · <?= date('M d, Y H:i', strtotime($s['scheduled_at'])) ?></p>
              </div>
              <a href="<?= htmlspecialchars($s['meet_link']) ?>" target="_blank"
                 class="text-xs bg-indigo-600 text-white px-3 py-1.5 rounded-lg hover:bg-indigo-700">
                Join
              </a>
            </div>
            <?php endforeach; ?>
          </div>
          <?php endif; ?>
        </div>

        <!-- Leaderboard -->
        <div class="bg-white rounded-2xl border border-gray-100 p-5">
          <h2 class="font-semibold text-gray-900 mb-4 flex items-center gap-2">
            <i data-lucide="trophy" class="w-4 h-4 text-amber-500"></i> Leaderboard
          </h2>
          <?php if (empty($lb)): ?>
          <p class="text-center text-gray-400 text-sm py-8">No rankings yet</p>
          <?php else: ?>
          <!-- Top 3 podium -->
          <?php
          $p = array_slice($lb, 0, 3);
          $order = [$p[1] ?? null, $p[0] ?? null, $p[2] ?? null];
          $heights = ['h-14', 'h-20', 'h-12'];
          $colors  = ['bg-gray-200 text-gray-700', 'bg-amber-400 text-white', 'bg-amber-700 text-white'];
          ?>
          <div class="flex items-end justify-center gap-2 mb-5">
            <?php foreach ($order as $pi => $person): if (!$person) continue; ?>
            <div class="flex flex-col items-center gap-1">
              <?php if ($person['profile_photo']): ?>
              <img src="<?= htmlspecialchars($person['profile_photo']) ?>" class="w-9 h-9 rounded-full object-cover border-2 border-white shadow" />
              <?php else: ?>
              <div class="w-9 h-9 bg-indigo-100 rounded-full flex items-center justify-center text-indigo-600 font-bold text-sm">
                <?= strtoupper(substr($person['full_name'],0,1)) ?>
              </div>
              <?php endif; ?>
              <p class="text-xs font-medium text-gray-700 truncate max-w-16 text-center"><?= htmlspecialchars(explode(' ',$person['full_name'])[0]) ?></p>
              <p class="text-xs text-indigo-600 font-semibold"><?= number_format($person['points']) ?></p>
              <div class="w-14 <?= $heights[$pi] ?> <?= $colors[$pi] ?> rounded-t-lg flex items-center justify-center font-bold text-sm">
                <?= $person['rnk'] ?>
              </div>
            </div>
            <?php endforeach; ?>
          </div>
          <!-- Rest of leaderboard -->
          <div class="space-y-1.5">
            <?php foreach (array_slice($lb, 3) as $person): ?>
            <div class="flex items-center gap-3 py-2 px-2 rounded-xl hover:bg-gray-50">
              <span class="text-sm font-bold text-gray-400 w-5 text-center"><?= $person['rnk'] ?></span>
              <?php if ($person['profile_photo']): ?>
              <img src="<?= htmlspecialchars($person['profile_photo']) ?>" class="w-7 h-7 rounded-full object-cover" />
              <?php else: ?>
              <div class="w-7 h-7 bg-indigo-100 rounded-full flex items-center justify-center text-indigo-600 text-xs font-bold">
                <?= strtoupper(substr($person['full_name'],0,1)) ?>
              </div>
              <?php endif; ?>
              <span class="text-sm text-gray-700 flex-1 truncate"><?= htmlspecialchars($person['full_name']) ?></span>
              <span class="text-sm font-semibold text-indigo-600"><?= number_format($person['points']) ?></span>
            </div>
            <?php endforeach; ?>
            <!-- My position -->
            <div class="flex items-center gap-3 py-2 px-2 rounded-xl bg-indigo-50 border border-indigo-100 mt-2">
              <span class="text-sm font-bold text-indigo-600 w-5 text-center"><?= $rank ?></span>
              <?php if ($me['profile_photo']): ?>
              <img src="<?= htmlspecialchars($me['profile_photo']) ?>" class="w-7 h-7 rounded-full object-cover border border-indigo-300" />
              <?php else: ?>
              <div class="w-7 h-7 bg-indigo-600 rounded-full flex items-center justify-center text-white text-xs font-bold">
                <?= strtoupper(substr($me['full_name'],0,1)) ?>
              </div>
              <?php endif; ?>
              <span class="text-sm text-indigo-700 font-semibold flex-1">You</span>
              <span class="text-sm font-bold text-indigo-600"><?= number_format($me['points']) ?></span>
            </div>
          </div>
          <?php endif; ?>
        </div>
      </div>
    </main>
    <?php require_once $_SERVER['DOCUMENT_ROOT'] . '/ByteClass/includes/footer-minimal.php'; ?>
  </div>
</div>
<?php require_once $_SERVER['DOCUMENT_ROOT'] . '/ByteClass/includes/scripts.php'; ?>
