<?php
$required_role = 'student';
require_once $_SERVER['DOCUMENT_ROOT'] . '/ByteClass/includes/auth-check.php';
require_once $_SERVER['DOCUMENT_ROOT'] . '/ByteClass/config/constants.php';
require_once $_SERVER['DOCUMENT_ROOT'] . '/ByteClass/config/database.php';
require_once $_SERVER['DOCUMENT_ROOT'] . '/ByteClass/helpers/response.php';

$db = Database::getInstance()->getConnection();
$id = (int)$_SESSION['user_id'];

$certs = $db->prepare("
    SELECT c.*, co.name AS course_name, co.thumbnail
    FROM certificates c
    JOIN courses co ON co.id=c.course_id
    WHERE c.student_id=?
    ORDER BY c.issued_at DESC
");
$certs->execute([$id]);
$certificates = $certs->fetchAll();

$page_title = 'My Certificates';
require_once $_SERVER['DOCUMENT_ROOT'] . '/ByteClass/includes/head.php';
?>
<div class="flex min-h-screen bg-gray-50">
  <?php require_once $_SERVER['DOCUMENT_ROOT'] . '/ByteClass/includes/sidebar-student.php'; ?>
  <div id="main-content" class="flex-1 flex flex-col" style="margin-left:256px;">
    <?php require_once $_SERVER['DOCUMENT_ROOT'] . '/ByteClass/includes/navbar-student.php'; ?>
    <main class="flex-1 p-6">
      <div class="bg-gradient-to-r from-indigo-600 to-cyan-500 rounded-2xl px-6 py-5 mb-6">
        <h2 class="text-white text-xl font-bold">My Certificates</h2>
        <p class="text-indigo-100 text-sm mt-0.5">Your earned certificates of completion</p>
      </div>
      <?php if (empty($certificates)): ?>
      <div class="bg-white rounded-2xl border border-gray-100 p-16 text-center">
        <i data-lucide="award" class="w-12 h-12 text-gray-300 mx-auto mb-4"></i>
        <h3 class="text-lg font-semibold text-gray-700 mb-2">No certificates yet</h3>
        <p class="text-gray-400 text-sm mb-6">Complete a course to earn your first certificate!</p>
        <a href="<?= APP_URL ?>/student/explore.php" class="inline-block bg-indigo-600 text-white px-6 py-3 rounded-xl font-medium hover:bg-indigo-700">Explore Courses</a>
      </div>
      <?php else: ?>
      <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 gap-5">
        <?php foreach ($certificates as $cert): ?>
        <div class="bg-white rounded-2xl border border-gray-100 overflow-hidden hover:shadow-md transition-shadow">
          <div class="bg-gradient-to-br from-indigo-600 to-cyan-500 p-6 text-center">
            <i data-lucide="award" class="w-12 h-12 text-white mx-auto mb-2"></i>
            <p class="text-white text-xs font-medium opacity-80">Certificate of Completion</p>
          </div>
          <div class="p-5">
            <h3 class="font-semibold text-gray-900 mb-1"><?= htmlspecialchars($cert['course_name']) ?></h3>
            <p class="text-xs text-gray-400 mb-1">ID: <?= htmlspecialchars($cert['certificate_uid']) ?></p>
            <p class="text-xs text-gray-400 mb-4">Issued: <?= date('M d, Y', strtotime($cert['issued_at'])) ?></p>
            <?php if ($cert['pdf_path']): ?>
            <a href="<?= htmlspecialchars($cert['pdf_path']) ?>" target="_blank"
               class="w-full bg-indigo-600 text-white py-2.5 rounded-xl text-sm font-medium hover:bg-indigo-700 flex items-center justify-center gap-2">
              <i data-lucide="download" class="w-4 h-4"></i> Download PDF
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
