<?php
$required_role = 'lecturer';
require_once $_SERVER['DOCUMENT_ROOT'] . '/ByteClass/includes/auth-check.php';
require_once $_SERVER['DOCUMENT_ROOT'] . '/ByteClass/config/constants.php';
require_once $_SERVER['DOCUMENT_ROOT'] . '/ByteClass/config/database.php';
require_once $_SERVER['DOCUMENT_ROOT'] . '/ByteClass/helpers/response.php';
$db = Database::getInstance()->getConnection();
$id = (int)$_SESSION['user_id'];

$course_filter = (int)($_GET['course_id'] ?? 0);
$search = sanitize($_GET['search'] ?? '');

$where = ['cl.lecturer_id=?']; $params = [$id];
if ($course_filter) { $where[] = 'c.id=?'; $params[] = $course_filter; }
if ($search) { $where[] = '(u.full_name LIKE ? OR u.email LIKE ?)'; $params[] = "%$search%"; $params[] = "%$search%"; }
$wsql = implode(' AND ', $where);

$stmt = $db->prepare("
    SELECT DISTINCT u.id, u.full_name, u.profile_photo, u.email, u.phone, u.points,
        MIN(e.enrolled_at) AS first_enrolled, MAX(e.enrolled_at) AS last_enrolled,
        COUNT(DISTINCT e.course_id) AS courses_count,
        c.name AS course_name
    FROM enrollments e
    JOIN users u ON u.id=e.student_id
    JOIN courses c ON c.id=e.course_id
    JOIN course_lecturers cl ON cl.course_id=c.id
    WHERE $wsql
    GROUP BY u.id ORDER BY first_enrolled DESC LIMIT 50
"); $stmt->execute($params); $students = $stmt->fetchAll();

$my_courses = $db->prepare("SELECT c.id, c.name FROM courses c JOIN course_lecturers cl ON cl.course_id=c.id WHERE cl.lecturer_id=? ORDER BY c.name");
$my_courses->execute([$id]); $my_courses = $my_courses->fetchAll();

$page_title = 'My Students';
require_once $_SERVER['DOCUMENT_ROOT'] . '/ByteClass/includes/head.php';
?>
<div class="flex min-h-screen bg-gray-50">
  <?php require_once $_SERVER['DOCUMENT_ROOT'] . '/ByteClass/includes/sidebar-lecturer.php'; ?>
  <div id="main-content" class="flex-1 flex flex-col" style="margin-left:256px;">
    <?php require_once $_SERVER['DOCUMENT_ROOT'] . '/ByteClass/includes/navbar-lecturer.php'; ?>
    <main class="flex-1 p-6">
      <div class="bg-gradient-to-r from-cyan-600 to-indigo-600 rounded-2xl px-6 py-5 mb-6">
        <h2 class="text-white text-xl font-bold">My Students</h2>
        <p class="text-cyan-100 text-sm mt-0.5"><?= count($students) ?> student<?= count($students)!=1?'s':'' ?> enrolled in your courses</p>
      </div>

      <form method="GET" class="bg-white rounded-2xl border border-gray-100 p-4 mb-5 flex flex-wrap gap-3">
        <div class="flex-1 min-w-48 relative">
          <i data-lucide="search" class="w-4 h-4 absolute left-3 top-1/2 -translate-y-1/2 text-gray-400"></i>
          <input type="text" name="search" value="<?= htmlspecialchars($search) ?>" placeholder="Search students..."
            class="w-full pl-9 pr-4 py-2.5 border border-gray-200 rounded-xl text-sm focus:outline-none focus:ring-2 focus:ring-indigo-500" />
        </div>
        <select name="course_id" class="px-4 py-2.5 border border-gray-200 rounded-xl text-sm focus:outline-none focus:ring-2 focus:ring-indigo-500">
          <option value="">All my courses</option>
          <?php foreach ($my_courses as $mc): ?>
          <option value="<?= $mc['id'] ?>" <?= $course_filter===$mc['id']?'selected':'' ?>><?= htmlspecialchars($mc['name']) ?></option>
          <?php endforeach; ?>
        </select>
        <button type="submit" class="bg-indigo-600 text-white px-4 py-2.5 rounded-xl text-sm font-medium hover:bg-indigo-700 flex items-center gap-2">
          <i data-lucide="filter" class="w-4 h-4"></i> Filter
        </button>
        <?php if ($search||$course_filter): ?><a href="?" class="px-4 py-2.5 border border-gray-200 rounded-xl text-sm text-gray-600 hover:bg-gray-50">Clear</a><?php endif; ?>
      </form>

      <div class="bg-white rounded-2xl border border-gray-100 overflow-hidden">
        <?php if (empty($students)): ?>
        <div class="p-12 text-center"><i data-lucide="users" class="w-10 h-10 text-gray-300 mx-auto mb-3"></i><p class="text-gray-400 text-sm">No students found</p></div>
        <?php else: ?>
        <table class="w-full">
          <thead class="bg-gray-50 border-b border-gray-100">
            <tr>
              <th class="px-5 py-3 text-left text-xs font-semibold text-gray-500 uppercase">Student</th>
              <th class="px-5 py-3 text-left text-xs font-semibold text-gray-500 uppercase">Contact</th>
              <th class="px-5 py-3 text-left text-xs font-semibold text-gray-500 uppercase">Courses</th>
              <th class="px-5 py-3 text-left text-xs font-semibold text-gray-500 uppercase">Points</th>
              <th class="px-5 py-3 text-left text-xs font-semibold text-gray-500 uppercase">Enrolled</th>
            </tr>
          </thead>
          <tbody class="divide-y divide-gray-50">
            <?php foreach ($students as $s): ?>
            <tr class="hover:bg-gray-50">
              <td class="px-5 py-4">
                <div class="flex items-center gap-3">
                  <?php if ($s['profile_photo']): ?>
                  <img src="<?= htmlspecialchars($s['profile_photo']) ?>" class="w-9 h-9 rounded-full object-cover flex-shrink-0" />
                  <?php else: ?>
                  <div class="w-9 h-9 bg-indigo-100 rounded-full flex items-center justify-center text-indigo-600 font-bold text-sm flex-shrink-0">
                    <?= strtoupper(substr($s['full_name'],0,1)) ?>
                  </div>
                  <?php endif; ?>
                  <div>
                    <p class="text-sm font-medium text-gray-900"><?= htmlspecialchars($s['full_name']) ?></p>
                    <p class="text-xs text-gray-400"><?= htmlspecialchars($s['email']) ?></p>
                  </div>
                </div>
              </td>
              <td class="px-5 py-4 text-sm text-gray-600"><?= htmlspecialchars($s['phone']) ?></td>
              <td class="px-5 py-4"><span class="text-sm font-semibold text-gray-900"><?= $s['courses_count'] ?></span><span class="text-xs text-gray-400"> course<?= $s['courses_count']!=1?'s':'' ?></span></td>
              <td class="px-5 py-4"><span class="text-sm font-semibold text-indigo-600"><?= number_format($s['points']) ?></span></td>
              <td class="px-5 py-4 text-sm text-gray-500"><?= date('M d, Y', strtotime($s['first_enrolled'])) ?></td>
            </tr>
            <?php endforeach; ?>
          </tbody>
        </table>
        <?php endif; ?>
      </div>
    </main>
    <?php require_once $_SERVER['DOCUMENT_ROOT'] . '/ByteClass/includes/footer-minimal.php'; ?>
  </div>
</div>
<?php require_once $_SERVER['DOCUMENT_ROOT'] . '/ByteClass/includes/scripts.php'; ?>
