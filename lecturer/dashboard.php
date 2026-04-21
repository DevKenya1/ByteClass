<?php
$required_role = 'lecturer';
require_once $_SERVER['DOCUMENT_ROOT'] . '/ByteClass/includes/auth-check.php';
require_once $_SERVER['DOCUMENT_ROOT'] . '/ByteClass/config/constants.php';
require_once $_SERVER['DOCUMENT_ROOT'] . '/ByteClass/config/database.php';
require_once $_SERVER['DOCUMENT_ROOT'] . '/ByteClass/helpers/response.php';

$db = Database::getInstance()->getConnection();
$id = (int)$_SESSION['user_id'];

// Stats
$s1 = $db->prepare("SELECT COUNT(DISTINCT e.student_id) FROM enrollments e JOIN courses c ON e.course_id=c.id JOIN course_lecturers cl ON cl.course_id=c.id WHERE cl.lecturer_id=?"); $s1->execute([$id]); $total_students = (int)$s1->fetchColumn();
$s2 = $db->prepare("SELECT COUNT(*) FROM courses c JOIN course_lecturers cl ON cl.course_id=c.id WHERE cl.lecturer_id=?"); $s2->execute([$id]); $total_courses = (int)$s2->fetchColumn();
$s3 = $db->prepare("SELECT COUNT(*) FROM courses c JOIN course_lecturers cl ON cl.course_id=c.id WHERE cl.lecturer_id=? AND c.status='published'"); $s3->execute([$id]); $published = (int)$s3->fetchColumn();
$s4 = $db->prepare("SELECT COUNT(*) FROM leave_requests WHERE lecturer_id=? AND status='pending'"); $s4->execute([$id]); $pending_leave = (int)$s4->fetchColumn();

// My courses
$courses_stmt = $db->prepare("
    SELECT c.id, c.name, c.thumbnail, c.status, c.difficulty, c.category,
        (SELECT COUNT(*) FROM enrollments e WHERE e.course_id=c.id) AS enrolled,
        (SELECT COUNT(*) FROM modules m WHERE m.course_id=c.id) AS modules,
        (SELECT COUNT(*) FROM lessons l JOIN modules m ON l.module_id=m.id WHERE m.course_id=c.id AND l.status='published') AS lessons
    FROM courses c JOIN course_lecturers cl ON cl.course_id=c.id
    WHERE cl.lecturer_id=? ORDER BY c.created_at DESC LIMIT 6
"); $courses_stmt->execute([$id]); $my_courses = $courses_stmt->fetchAll();

// Recent students
$students_stmt = $db->prepare("
    SELECT DISTINCT u.id, u.full_name, u.profile_photo, u.email, e.enrolled_at, c.name AS course_name
    FROM enrollments e
    JOIN users u ON u.id=e.student_id
    JOIN courses c ON c.id=e.course_id
    JOIN course_lecturers cl ON cl.course_id=c.id
    WHERE cl.lecturer_id=?
    ORDER BY e.enrolled_at DESC LIMIT 5
"); $students_stmt->execute([$id]); $recent_students = $students_stmt->fetchAll();

// Upcoming sessions
$sessions_stmt = $db->prepare("
    SELECT cs.id, cs.title, cs.scheduled_at, cs.duration_min, cs.platform, c.name AS course_name
    FROM class_sessions cs JOIN courses c ON cs.course_id=c.id
    JOIN course_lecturers cl ON cl.course_id=c.id
    WHERE cl.lecturer_id=? AND cs.scheduled_at>=NOW() AND cs.status='upcoming'
    ORDER BY cs.scheduled_at ASC LIMIT 3
"); $sessions_stmt->execute([$id]); $upcoming = $sessions_stmt->fetchAll();

// Recent reviews
$reviews_stmt = $db->prepare("
    SELECT lr.rating, lr.comment, lr.strengths, lr.created_at, u.full_name AS reviewer
    FROM lecturer_reviews lr JOIN users u ON u.id=lr.reviewed_by
    WHERE lr.lecturer_id=? ORDER BY lr.created_at DESC LIMIT 3
"); $reviews_stmt->execute([$id]); $reviews = $reviews_stmt->fetchAll();

$avg_rating_row = $db->prepare("SELECT AVG(rating) FROM lecturer_reviews WHERE lecturer_id=?");
$avg_rating_row->execute([$id]); $avg_rating = round((float)$avg_rating_row->fetchColumn(),1);

$page_title = 'Lecturer Dashboard';
require_once $_SERVER['DOCUMENT_ROOT'] . '/ByteClass/includes/head.php';
?>
<div class="flex min-h-screen bg-gray-50">
  <?php require_once $_SERVER['DOCUMENT_ROOT'] . '/ByteClass/includes/sidebar-lecturer.php'; ?>
  <div id="main-content" class="flex-1 flex flex-col" style="margin-left:256px;">
    <?php require_once $_SERVER['DOCUMENT_ROOT'] . '/ByteClass/includes/navbar-lecturer.php'; ?>
    <main class="flex-1 p-6">

      <!-- Welcome banner -->
      <div class="bg-gradient-to-r from-cyan-600 to-indigo-600 rounded-2xl px-6 py-5 mb-6 flex items-center justify-between">
        <div>
          <h2 class="text-white text-xl font-bold">Welcome back, <?= htmlspecialchars(explode(' ',$_SESSION['full_name'])[0]) ?> 👋</h2>
          <p class="text-cyan-100 text-sm mt-0.5">Lecturer Dashboard — ByteClass</p>
        </div>
        <?php if ($_SESSION['photo']): ?>
        <img src="<?= htmlspecialchars($_SESSION['photo']) ?>" class="w-14 h-14 rounded-2xl object-cover border-2 border-white/30" />
        <?php else: ?>
        <div class="w-14 h-14 bg-white/20 rounded-2xl flex items-center justify-center text-white text-xl font-bold">
          <?= strtoupper(substr($_SESSION['full_name'],0,1)) ?>
        </div>
        <?php endif; ?>
      </div>

      <!-- Stats -->
      <div class="grid grid-cols-2 lg:grid-cols-4 gap-4 mb-6">
        <?php foreach ([
          ['label'=>'My Students',      'value'=>$total_students, 'icon'=>'users',        'color'=>'bg-indigo-500'],
          ['label'=>'My Courses',       'value'=>$total_courses,  'icon'=>'book-open',    'color'=>'bg-cyan-500'],
          ['label'=>'Published',        'value'=>$published,      'icon'=>'globe',        'color'=>'bg-green-500'],
          ['label'=>'Avg Rating',       'value'=>$avg_rating > 0 ? $avg_rating.'/5' : 'N/A', 'icon'=>'star', 'color'=>'bg-amber-500'],
        ] as $s): ?>
        <div class="bg-white rounded-2xl p-5 border border-gray-100 flex items-center gap-4">
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

        <!-- My Courses -->
        <div class="lg:col-span-2 space-y-6">
          <div>
            <div class="flex items-center justify-between mb-4">
              <h2 class="font-semibold text-gray-900">My Courses</h2>
              <a href="<?= APP_URL ?>/lecturer/courses.php" class="text-sm text-indigo-600 hover:underline">View all</a>
            </div>
            <?php if (empty($my_courses)): ?>
            <div class="bg-white rounded-2xl border border-gray-100 p-10 text-center">
              <i data-lucide="book-open" class="w-10 h-10 text-gray-300 mx-auto mb-3"></i>
              <p class="text-gray-500 text-sm">No courses assigned yet. Contact your administrator.</p>
            </div>
            <?php else: ?>
            <div class="space-y-3">
              <?php foreach ($my_courses as $c):
                $sc = match($c['status']) { 'published'=>'bg-green-100 text-green-700','draft'=>'bg-amber-100 text-amber-700',default=>'bg-gray-100 text-gray-600'};
                $dc = match($c['difficulty']) { 'beginner'=>'bg-blue-100 text-blue-700','intermediate'=>'bg-orange-100 text-orange-700',default=>'bg-red-100 text-red-700'};
              ?>
              <div class="bg-white rounded-xl border border-gray-100 p-4 flex items-center gap-4 hover:shadow-sm transition-shadow">
                <div class="w-14 h-12 rounded-xl overflow-hidden flex-shrink-0">
                  <?php if ($c['thumbnail']): ?>
                  <img src="<?= htmlspecialchars($c['thumbnail']) ?>" class="w-full h-full object-cover" />
                  <?php else: ?>
                  <div class="w-full h-full bg-gradient-to-br from-cyan-500 to-indigo-600 flex items-center justify-center">
                    <i data-lucide="book-open" class="w-5 h-5 text-white opacity-60"></i>
                  </div>
                  <?php endif; ?>
                </div>
                <div class="flex-1 min-w-0">
                  <p class="font-semibold text-gray-900 text-sm truncate"><?= htmlspecialchars($c['name']) ?></p>
                  <div class="flex items-center gap-2 mt-1">
                    <span class="text-xs px-2 py-0.5 rounded-full <?= $sc ?>"><?= ucfirst($c['status']) ?></span>
                    <span class="text-xs px-2 py-0.5 rounded-full <?= $dc ?>"><?= ucfirst($c['difficulty']) ?></span>
                  </div>
                </div>
                <div class="text-right flex-shrink-0">
                  <p class="text-sm font-bold text-gray-900"><?= $c['enrolled'] ?></p>
                  <p class="text-xs text-gray-400">students</p>
                </div>
              </div>
              <?php endforeach; ?>
            </div>
            <?php endif; ?>
          </div>

          <!-- Recent Students -->
          <?php if (!empty($recent_students)): ?>
          <div>
            <div class="flex items-center justify-between mb-4">
              <h2 class="font-semibold text-gray-900">Recent Students</h2>
              <a href="<?= APP_URL ?>/lecturer/students.php" class="text-sm text-indigo-600 hover:underline">View all</a>
            </div>
            <div class="bg-white rounded-2xl border border-gray-100 overflow-hidden">
              <table class="w-full">
                <thead class="bg-gray-50 border-b border-gray-100">
                  <tr>
                    <th class="px-4 py-3 text-left text-xs font-semibold text-gray-500 uppercase">Student</th>
                    <th class="px-4 py-3 text-left text-xs font-semibold text-gray-500 uppercase">Course</th>
                    <th class="px-4 py-3 text-left text-xs font-semibold text-gray-500 uppercase">Enrolled</th>
                  </tr>
                </thead>
                <tbody class="divide-y divide-gray-50">
                  <?php foreach ($recent_students as $s): ?>
                  <tr class="hover:bg-gray-50">
                    <td class="px-4 py-3">
                      <div class="flex items-center gap-3">
                        <?php if ($s['profile_photo']): ?>
                        <img src="<?= htmlspecialchars($s['profile_photo']) ?>" class="w-8 h-8 rounded-full object-cover flex-shrink-0" />
                        <?php else: ?>
                        <div class="w-8 h-8 bg-indigo-100 rounded-full flex items-center justify-center text-indigo-600 font-bold text-xs flex-shrink-0">
                          <?= strtoupper(substr($s['full_name'],0,1)) ?>
                        </div>
                        <?php endif; ?>
                        <div class="min-w-0">
                          <p class="text-sm font-medium text-gray-900 truncate"><?= htmlspecialchars($s['full_name']) ?></p>
                          <p class="text-xs text-gray-400 truncate"><?= htmlspecialchars($s['email']) ?></p>
                        </div>
                      </div>
                    </td>
                    <td class="px-4 py-3 text-sm text-gray-600 truncate max-w-32"><?= htmlspecialchars($s['course_name']) ?></td>
                    <td class="px-4 py-3 text-sm text-gray-500 whitespace-nowrap"><?= date('M d, Y', strtotime($s['enrolled_at'])) ?></td>
                  </tr>
                  <?php endforeach; ?>
                </tbody>
              </table>
            </div>
          </div>
          <?php endif; ?>
        </div>

        <!-- Right column -->
        <div class="space-y-6">

          <!-- Upcoming Sessions -->
          <div>
            <div class="flex items-center justify-between mb-4">
              <h2 class="font-semibold text-gray-900">Upcoming Sessions</h2>
              <a href="<?= APP_URL ?>/lecturer/sessions.php" class="text-sm text-indigo-600 hover:underline">View all</a>
            </div>
            <?php if (empty($upcoming)): ?>
            <div class="bg-white rounded-2xl border border-gray-100 p-6 text-center">
              <i data-lucide="calendar" class="w-8 h-8 text-gray-300 mx-auto mb-2"></i>
              <p class="text-gray-400 text-sm">No upcoming sessions</p>
              <a href="<?= APP_URL ?>/lecturer/sessions.php" class="text-xs text-indigo-600 hover:underline mt-1 block">Schedule one →</a>
            </div>
            <?php else: foreach ($upcoming as $s): ?>
            <div class="bg-white rounded-xl border border-gray-100 p-4 mb-3">
              <p class="font-semibold text-gray-900 text-sm"><?= htmlspecialchars($s['title']) ?></p>
              <p class="text-xs text-gray-400 mt-0.5"><?= htmlspecialchars($s['course_name']) ?></p>
              <div class="flex items-center justify-between mt-2">
                <span class="text-xs text-indigo-600 font-medium"><?= date('M d, H:i', strtotime($s['scheduled_at'])) ?></span>
                <span class="text-xs bg-cyan-100 text-cyan-700 px-2 py-0.5 rounded-full"><?= $s['duration_min'] ?>min</span>
              </div>
            </div>
            <?php endforeach; endif; ?>
          </div>

          <!-- Performance Reviews -->
          <div>
            <div class="flex items-center justify-between mb-4">
              <h2 class="font-semibold text-gray-900">My Reviews</h2>
              <a href="<?= APP_URL ?>/lecturer/reviews.php" class="text-sm text-indigo-600 hover:underline">View all</a>
            </div>
            <?php if (empty($reviews)): ?>
            <div class="bg-white rounded-2xl border border-gray-100 p-6 text-center">
              <i data-lucide="star" class="w-8 h-8 text-gray-300 mx-auto mb-2"></i>
              <p class="text-gray-400 text-sm">No reviews yet</p>
            </div>
            <?php else: foreach ($reviews as $r): ?>
            <div class="bg-white rounded-xl border border-gray-100 p-4 mb-3">
              <div class="flex items-center gap-1 mb-2">
                <?php for($i=1;$i<=5;$i++): ?>
                <i data-lucide="star" class="w-3.5 h-3.5 <?= $i<=$r['rating'] ? 'text-amber-400 fill-amber-400' : 'text-gray-200' ?>"></i>
                <?php endfor; ?>
                <span class="text-xs text-gray-500 ml-1"><?= $r['rating'] ?>/5</span>
              </div>
              <?php if ($r['strengths']): ?>
              <p class="text-xs text-gray-600 mb-1"><span class="text-green-600 font-semibold">Strength:</span> <?= htmlspecialchars(substr($r['strengths'],0,80)) ?></p>
              <?php endif; ?>
              <?php if ($r['comment']): ?>
              <p class="text-xs text-gray-500"><?= htmlspecialchars(substr($r['comment'],0,80)) ?></p>
              <?php endif; ?>
              <p class="text-xs text-gray-400 mt-1">By <?= htmlspecialchars($r['reviewer']) ?> · <?= date('M d, Y', strtotime($r['created_at'])) ?></p>
            </div>
            <?php endforeach; endif; ?>
          </div>

          <!-- Quick links -->
          <?php if ($pending_leave > 0): ?>
          <div class="bg-amber-50 border border-amber-200 rounded-2xl p-4 flex items-center gap-3">
            <i data-lucide="clock" class="w-5 h-5 text-amber-600 flex-shrink-0"></i>
            <div>
              <p class="text-sm font-semibold text-amber-800">Leave request pending</p>
              <a href="<?= APP_URL ?>/lecturer/hr.php" class="text-xs text-amber-600 hover:underline">View status →</a>
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
