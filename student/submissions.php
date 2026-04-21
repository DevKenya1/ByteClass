<?php
$required_role = 'student';
require_once $_SERVER['DOCUMENT_ROOT'] . '/ByteClass/includes/auth-check.php';
require_once $_SERVER['DOCUMENT_ROOT'] . '/ByteClass/config/constants.php';
require_once $_SERVER['DOCUMENT_ROOT'] . '/ByteClass/config/database.php';
require_once $_SERVER['DOCUMENT_ROOT'] . '/ByteClass/helpers/response.php';

$db = Database::getInstance()->getConnection();
$id = (int)$_SESSION['user_id'];

$attempts = $db->prepare("
    SELECT qa.*, q.title AS quiz_title, q.passmark,
        l.title AS lesson_title, c.name AS course_name
    FROM quiz_attempts qa
    JOIN quizzes q ON q.id=qa.quiz_id
    LEFT JOIN lessons l ON l.id=q.lesson_id
    LEFT JOIN modules m ON m.id=l.module_id
    LEFT JOIN courses c ON c.id=m.course_id
    WHERE qa.student_id=?
    ORDER BY qa.attempted_at DESC
    LIMIT 50
");
$attempts->execute([$id]);
$submissions = $attempts->fetchAll();

$page_title = 'Submissions';
require_once $_SERVER['DOCUMENT_ROOT'] . '/ByteClass/includes/head.php';
?>
<div class="flex min-h-screen bg-gray-50">
  <?php require_once $_SERVER['DOCUMENT_ROOT'] . '/ByteClass/includes/sidebar-student.php'; ?>
  <div id="main-content" class="flex-1 flex flex-col" style="margin-left:256px;">
    <?php require_once $_SERVER['DOCUMENT_ROOT'] . '/ByteClass/includes/navbar-student.php'; ?>
    <main class="flex-1 p-6">
      <div class="bg-gradient-to-r from-indigo-600 to-cyan-500 rounded-2xl px-6 py-5 mb-6">
        <h2 class="text-white text-xl font-bold">Quiz Submissions</h2>
        <p class="text-indigo-100 text-sm mt-0.5">Your quiz attempts and results</p>
      </div>
      <?php if (empty($submissions)): ?>
      <div class="bg-white rounded-2xl border border-gray-100 p-12 text-center">
        <i data-lucide="clipboard-list" class="w-10 h-10 text-gray-300 mx-auto mb-3"></i>
        <p class="text-gray-500">No quiz submissions yet</p>
        <p class="text-gray-400 text-sm mt-1">Complete lessons and take quizzes to see results here</p>
      </div>
      <?php else: ?>
      <div class="bg-white rounded-2xl border border-gray-100 overflow-hidden">
        <table class="w-full">
          <thead class="bg-gray-50 border-b border-gray-100">
            <tr>
              <th class="px-5 py-3 text-left text-xs font-semibold text-gray-500 uppercase">Quiz</th>
              <th class="px-5 py-3 text-left text-xs font-semibold text-gray-500 uppercase">Course</th>
              <th class="px-5 py-3 text-left text-xs font-semibold text-gray-500 uppercase">Score</th>
              <th class="px-5 py-3 text-left text-xs font-semibold text-gray-500 uppercase">Result</th>
              <th class="px-5 py-3 text-left text-xs font-semibold text-gray-500 uppercase">Date</th>
            </tr>
          </thead>
          <tbody class="divide-y divide-gray-50">
            <?php foreach ($submissions as $s): ?>
            <tr class="hover:bg-gray-50">
              <td class="px-5 py-4">
                <p class="text-sm font-medium text-gray-900"><?= htmlspecialchars($s['quiz_title']) ?></p>
                <?php if ($s['lesson_title']): ?><p class="text-xs text-gray-400"><?= htmlspecialchars($s['lesson_title']) ?></p><?php endif; ?>
              </td>
              <td class="px-5 py-4 text-sm text-gray-600"><?= htmlspecialchars($s['course_name'] ?? 'N/A') ?></td>
              <td class="px-5 py-4">
                <div class="flex items-center gap-2">
                  <div class="w-16 bg-gray-100 rounded-full h-2">
                    <div class="<?= $s['passed'] ? 'bg-green-500' : 'bg-red-400' ?> h-2 rounded-full" style="width:<?= min(100,$s['score']) ?>%"></div>
                  </div>
                  <span class="text-sm font-semibold <?= $s['passed'] ? 'text-green-600' : 'text-red-500' ?>"><?= number_format($s['score'],1) ?>%</span>
                </div>
                <p class="text-xs text-gray-400 mt-0.5">Pass mark: <?= $s['passmark'] ?>%</p>
              </td>
              <td class="px-5 py-4">
                <span class="px-2.5 py-1 rounded-full text-xs font-medium <?= $s['passed'] ? 'bg-green-100 text-green-700' : 'bg-red-100 text-red-700' ?>">
                  <?= $s['passed'] ? '✓ Passed' : '✗ Failed' ?>
                </span>
              </td>
              <td class="px-5 py-4 text-sm text-gray-500"><?= date('M d, Y H:i', strtotime($s['attempted_at'])) ?></td>
            </tr>
            <?php endforeach; ?>
          </tbody>
        </table>
      </div>
      <?php endif; ?>
    </main>
    <?php require_once $_SERVER['DOCUMENT_ROOT'] . '/ByteClass/includes/footer-minimal.php'; ?>
  </div>
</div>
<?php require_once $_SERVER['DOCUMENT_ROOT'] . '/ByteClass/includes/scripts.php'; ?>
