<?php
$required_role = 'student';
require_once $_SERVER['DOCUMENT_ROOT'] . '/ByteClass/includes/auth-check.php';
require_once $_SERVER['DOCUMENT_ROOT'] . '/ByteClass/config/constants.php';
require_once $_SERVER['DOCUMENT_ROOT'] . '/ByteClass/config/database.php';
require_once $_SERVER['DOCUMENT_ROOT'] . '/ByteClass/helpers/response.php';
require_once $_SERVER['DOCUMENT_ROOT'] . '/ByteClass/helpers/points.php';

$db         = Database::getInstance()->getConnection();
$student_id = (int)$_SESSION['user_id'];
$quiz_id    = (int)($_GET['quiz_id']    ?? 0);
$course_id  = (int)($_GET['course_id'] ?? 0);

if (!$quiz_id) { header('Location: '.APP_URL.'/student/courses.php'); exit; }

// Load quiz
$quiz_stmt = $db->prepare("SELECT q.*, l.id AS lesson_id, l.title AS lesson_title, c.id AS course_id, c.name AS course_name
    FROM quizzes q JOIN lessons l ON l.id=q.lesson_id
    JOIN modules m ON m.id=l.module_id JOIN courses c ON c.id=m.course_id
    WHERE q.id=? LIMIT 1");
$quiz_stmt->execute([$quiz_id]); $quiz = $quiz_stmt->fetch();
if (!$quiz) { header('Location: '.APP_URL.'/student/courses.php'); exit; }

// Verify enrolled
$enroll = $db->prepare("SELECT id FROM enrollments WHERE student_id=? AND course_id=?");
$enroll->execute([$student_id, $quiz['course_id']]);
if (!$enroll->fetch()) { header('Location: '.APP_URL.'/student/explore.php'); exit; }

// Load questions + options
$q_stmt = $db->prepare("SELECT qq.id, qq.question, qq.sort_order FROM quiz_questions qq WHERE qq.quiz_id=? ORDER BY qq.sort_order ASC");
$q_stmt->execute([$quiz_id]); $questions = $q_stmt->fetchAll();
foreach ($questions as &$q) {
    $o_stmt = $db->prepare("SELECT id, option_text, sort_order FROM quiz_options WHERE question_id=? ORDER BY sort_order ASC");
    $o_stmt->execute([$q['id']]); $q['options'] = $o_stmt->fetchAll();
}
unset($q);

// Last attempt
$last_attempt = $db->prepare("SELECT * FROM quiz_attempts WHERE quiz_id=? AND student_id=? ORDER BY attempted_at DESC LIMIT 1");
$last_attempt->execute([$quiz_id, $student_id]); $last_attempt = $last_attempt->fetch();

$attempt_count = (int)$db->prepare("SELECT COUNT(*) FROM quiz_attempts WHERE quiz_id=? AND student_id=?")->execute([$quiz_id,$student_id])
    ? (int)$db->query("SELECT COUNT(*) FROM quiz_attempts WHERE quiz_id=$quiz_id AND student_id=$student_id")->fetchColumn() : 0;

// ── Handle quiz submission ────────────────────────────────────────────────────
$result = null;
if ($_SERVER['REQUEST_METHOD'] === 'POST' && ($_POST['action'] ?? '') === 'submit_quiz') {
    $answers    = $_POST['answers'] ?? [];  // [question_id => option_id]
    $correct    = 0;
    $total      = count($questions);
    $answers_log = [];

    foreach ($questions as $q) {
        $selected_opt = (int)($answers[$q['id']] ?? 0);
        // Check if selected option is correct
        $is_correct = 0;
        foreach ($q['options'] as $opt) {
            if ((int)$opt['id'] === $selected_opt) {
                // Check is_correct in DB
                $chk = $db->prepare("SELECT is_correct FROM quiz_options WHERE id=?");
                $chk->execute([$opt['id']]); $chk_row = $chk->fetch();
                $is_correct = (int)($chk_row['is_correct'] ?? 0);
                break;
            }
        }
        if ($is_correct) $correct++;
        $answers_log[$q['id']] = ['selected' => $selected_opt, 'correct' => $is_correct];
    }

    $score   = $total > 0 ? round(($correct / $total) * 100) : 0;
    $passed  = $score >= $quiz['pass_mark'] ? 1 : 0;

    // Save attempt
    $db->prepare("INSERT INTO quiz_attempts (quiz_id, student_id, score, total, passed, answers_json) VALUES (?,?,?,?,?,?)")
       ->execute([$quiz_id, $student_id, $score, $total, $passed, json_encode($answers_log)]);

    // Award points if passed (once per quiz)
    if ($passed) {
        $pts_chk = $db->prepare("SELECT 1 FROM activity_logs WHERE user_id=? AND action='quiz_pass' AND description LIKE ? LIMIT 1");
        $pts_chk->execute([$student_id, "%quiz:$quiz_id%"]);
        if (!$pts_chk->fetch()) {
            award_points($student_id, 100, "Quiz passed — quiz:$quiz_id");
            $db->prepare("INSERT INTO activity_logs (user_id,action,description,ip_address) VALUES (?,?,?,?)")
               ->execute([$student_id,'quiz_pass',"Passed quiz:$quiz_id",$_SERVER['REMOTE_ADDR']??'']);

            // Notify student
            $db->prepare("INSERT INTO notifications (user_id,title,message,type) VALUES (?,?,?,?)")
               ->execute([$student_id,'Quiz Passed! 🎉',"You passed \"{$quiz['title']}\" with $score%. +100 points awarded!",'course']);
        }
    }

    $result = [
        'correct' => $correct, 'total' => $total, 'score' => $score,
        'passed' => $passed, 'answers' => $answers_log,
        'pass_mark' => $quiz['pass_mark']
    ];
}

$page_title = $quiz['title'] . ' — Quiz';
require_once $_SERVER['DOCUMENT_ROOT'] . '/ByteClass/includes/head.php';
?>
<div class="flex min-h-screen bg-gray-50">
  <?php require_once $_SERVER['DOCUMENT_ROOT'] . '/ByteClass/includes/sidebar-student.php'; ?>
  <div id="main-content" class="flex-1 flex flex-col" style="margin-left:256px;">
    <?php require_once $_SERVER['DOCUMENT_ROOT'] . '/ByteClass/includes/navbar-student.php'; ?>
    <main class="flex-1 p-6 max-w-3xl">

      <!-- Back link -->
      <a href="<?= APP_URL ?>/student/lesson-player.php?course_id=<?= $quiz['course_id'] ?>&lesson_id=<?= $quiz['lesson_id'] ?>"
         class="inline-flex items-center gap-2 text-sm text-gray-500 hover:text-gray-800 mb-5 transition-colors">
        <i data-lucide="arrow-left" class="w-4 h-4"></i> Back to Lesson
      </a>

      <!-- Quiz header -->
      <div class="bg-gradient-to-r from-amber-500 to-orange-500 rounded-2xl px-6 py-5 mb-6">
        <div class="flex items-start justify-between gap-4">
          <div>
            <p class="text-amber-100 text-xs font-medium uppercase tracking-wide mb-1"><?= htmlspecialchars($quiz['course_name']) ?></p>
            <h1 class="text-white text-xl font-bold"><?= htmlspecialchars($quiz['title']) ?></h1>
            <p class="text-amber-200 text-sm mt-1 flex items-center gap-3">
              <span><?= count($questions) ?> questions</span>
              <span>·</span>
              <span>Pass mark: <?= $quiz['pass_mark'] ?>%</span>
              <?php if ($attempt_count > 0): ?>
              <span>·</span>
              <span><?= $attempt_count ?> previous attempt<?= $attempt_count!=1?'s':'' ?></span>
              <?php endif; ?>
            </p>
          </div>
          <div class="w-14 h-14 bg-white/20 rounded-2xl flex items-center justify-center flex-shrink-0">
            <i data-lucide="help-circle" class="w-8 h-8 text-white"></i>
          </div>
        </div>
      </div>

      <?php if (empty($questions)): ?>
      <!-- No questions yet -->
      <div class="bg-white rounded-2xl border border-gray-100 p-12 text-center">
        <i data-lucide="alert-circle" class="w-10 h-10 text-gray-300 mx-auto mb-3"></i>
        <h3 class="font-semibold text-gray-700 mb-1">No questions yet</h3>
        <p class="text-gray-400 text-sm">Your lecturer hasn't added questions to this quiz yet. Check back soon!</p>
      </div>

      <?php elseif ($result !== null): ?>
      <!-- RESULTS VIEW -->
      <div class="space-y-4">
        <!-- Score card -->
        <div class="bg-white rounded-2xl border <?= $result['passed'] ? 'border-green-200 bg-green-50/30' : 'border-red-200 bg-red-50/30' ?> p-6 text-center">
          <div class="w-24 h-24 rounded-full flex items-center justify-center mx-auto mb-4 <?= $result['passed'] ? 'bg-green-100' : 'bg-red-100' ?>">
            <div>
              <p class="text-3xl font-black <?= $result['passed'] ? 'text-green-600' : 'text-red-600' ?>"><?= $result['score'] ?>%</p>
            </div>
          </div>
          <div class="flex items-center justify-center gap-2 mb-2">
            <i data-lucide="<?= $result['passed'] ? 'check-circle' : 'x-circle' ?>"
               class="w-6 h-6 <?= $result['passed'] ? 'text-green-600' : 'text-red-600' ?>"></i>
            <h2 class="text-xl font-black <?= $result['passed'] ? 'text-green-700' : 'text-red-700' ?>">
              <?= $result['passed'] ? 'Quiz Passed! 🎉' : 'Not Passed' ?>
            </h2>
          </div>
          <p class="text-gray-500 text-sm mb-1">
            You answered <strong><?= $result['correct'] ?></strong> of <strong><?= $result['total'] ?></strong> questions correctly
          </p>
          <p class="text-gray-400 text-xs">Required: <?= $result['pass_mark'] ?>% · You scored: <?= $result['score'] ?>%</p>
          <?php if ($result['passed']): ?>
          <div class="mt-3 inline-flex items-center gap-2 bg-green-100 text-green-700 text-sm font-semibold px-4 py-2 rounded-full">
            <i data-lucide="zap" class="w-4 h-4"></i> +100 points awarded!
          </div>
          <?php endif; ?>
        </div>

        <!-- Answer review -->
        <div class="bg-white rounded-2xl border border-gray-100 overflow-hidden">
          <div class="px-5 py-3 border-b border-gray-100 bg-gray-50">
            <h3 class="font-semibold text-gray-800 text-sm">Answer Review</h3>
          </div>
          <div class="divide-y divide-gray-50">
            <?php foreach ($questions as $qi => $q):
              $ans_data = $result['answers'][$q['id']] ?? [];
              $selected_opt_id = (int)($ans_data['selected'] ?? 0);
              $was_correct = (int)($ans_data['correct'] ?? 0);
            ?>
            <div class="p-5">
              <div class="flex items-start gap-3 mb-3">
                <div class="w-7 h-7 rounded-xl flex items-center justify-center flex-shrink-0 <?= $was_correct ? 'bg-green-500' : 'bg-red-500' ?>">
                  <i data-lucide="<?= $was_correct ? 'check' : 'x' ?>" class="w-3.5 h-3.5 text-white"></i>
                </div>
                <p class="text-sm font-semibold text-gray-900 leading-snug"><?= htmlspecialchars($q['question']) ?></p>
              </div>
              <div class="grid grid-cols-1 gap-2 ml-10">
                <?php foreach ($q['options'] as $opt):
                  $is_user_choice = (int)$opt['id'] === $selected_opt_id;
                  // Fetch correct flag
                  $is_correct_opt = (int)$db->query("SELECT is_correct FROM quiz_options WHERE id={$opt['id']}")->fetchColumn();
                  $cls = 'bg-gray-50 border-gray-200 text-gray-600';
                  if ($is_correct_opt) $cls = 'bg-green-50 border-green-300 text-green-800 font-semibold';
                  if ($is_user_choice && !$is_correct_opt) $cls = 'bg-red-50 border-red-300 text-red-800';
                ?>
                <div class="flex items-center gap-2 px-3 py-2 rounded-xl border text-xs <?= $cls ?>">
                  <?php if ($is_correct_opt): ?><i data-lucide="check-circle" class="w-3.5 h-3.5 text-green-600 flex-shrink-0"></i>
                  <?php elseif ($is_user_choice): ?><i data-lucide="x-circle" class="w-3.5 h-3.5 text-red-500 flex-shrink-0"></i>
                  <?php else: ?><i data-lucide="circle" class="w-3.5 h-3.5 text-gray-300 flex-shrink-0"></i>
                  <?php endif; ?>
                  <?= htmlspecialchars($opt['option_text']) ?>
                  <?php if ($is_user_choice): ?><span class="ml-auto text-xs opacity-60 flex-shrink-0">Your answer</span><?php endif; ?>
                </div>
                <?php endforeach; ?>
              </div>
            </div>
            <?php endforeach; ?>
          </div>
        </div>

        <!-- Actions -->
        <div class="flex gap-3">
          <?php if (!$result['passed']): ?>
          <a href="?quiz_id=<?= $quiz_id ?>&course_id=<?= $course_id ?>"
             class="flex-1 bg-amber-500 text-white py-3 rounded-xl text-sm font-bold hover:bg-amber-600 transition-colors flex items-center justify-center gap-2">
            <i data-lucide="refresh-cw" class="w-4 h-4"></i> Try Again
          </a>
          <?php endif; ?>
          <a href="<?= APP_URL ?>/student/lesson-player.php?course_id=<?= $quiz['course_id'] ?>&lesson_id=<?= $quiz['lesson_id'] ?>"
             class="flex-1 <?= $result['passed'] ? 'bg-indigo-600 hover:bg-indigo-700' : 'bg-gray-200 hover:bg-gray-300' ?> text-<?= $result['passed'] ? 'white' : 'gray-700' ?> py-3 rounded-xl text-sm font-bold transition-colors flex items-center justify-center gap-2">
            <i data-lucide="arrow-left" class="w-4 h-4"></i> Back to Lesson
          </a>
        </div>
      </div>

      <?php else: ?>
      <!-- QUIZ FORM VIEW -->
      <?php if ($last_attempt): ?>
      <div class="bg-amber-50 border border-amber-200 rounded-xl p-4 mb-5 flex items-center gap-3">
        <i data-lucide="clock" class="w-5 h-5 text-amber-600 flex-shrink-0"></i>
        <div>
          <p class="text-sm font-semibold text-amber-800">Previous attempt</p>
          <p class="text-xs text-amber-600">Score: <?= $last_attempt['score'] ?>% · <?= $last_attempt['passed'] ? 'Passed ✓' : 'Not passed' ?> · <?= date('M d, Y H:i', strtotime($last_attempt['attempted_at'])) ?></p>
        </div>
      </div>
      <?php endif; ?>

      <form method="POST" id="quiz-form" onsubmit="return validateQuiz()">
        <input type="hidden" name="action" value="submit_quiz">
        <div class="space-y-5 mb-6">
          <?php foreach ($questions as $qi => $q): ?>
          <div class="bg-white rounded-2xl border border-gray-100 p-5 shadow-sm" id="q-<?= $q['id'] ?>">
            <div class="flex items-start gap-3 mb-4">
              <div class="w-8 h-8 bg-amber-500 rounded-xl flex items-center justify-center text-white text-sm font-bold flex-shrink-0">
                <?= $qi+1 ?>
              </div>
              <p class="text-gray-900 font-semibold leading-snug pt-1"><?= htmlspecialchars($q['question']) ?></p>
            </div>
            <div class="space-y-2 ml-11">
              <?php foreach ($q['options'] as $oi => $opt): ?>
              <label class="flex items-center gap-3 p-3 rounded-xl border border-gray-200 cursor-pointer hover:border-amber-400 hover:bg-amber-50 transition-all has-[:checked]:border-amber-500 has-[:checked]:bg-amber-50 group">
                <input type="radio" name="answers[<?= $q['id'] ?>]" value="<?= $opt['id'] ?>" required
                  class="w-4 h-4 text-amber-600 accent-amber-600 flex-shrink-0" />
                <span class="text-sm text-gray-700 group-hover:text-gray-900"><?= htmlspecialchars($opt['option_text']) ?></span>
              </label>
              <?php endforeach; ?>
            </div>
          </div>
          <?php endforeach; ?>
        </div>

        <!-- Submit -->
        <div class="bg-white rounded-2xl border border-gray-100 p-5 flex items-center justify-between gap-4">
          <div>
            <p class="text-sm font-semibold text-gray-900"><?= count($questions) ?> question<?= count($questions)!=1?'s':'' ?> · Pass mark: <?= $quiz['pass_mark'] ?>%</p>
            <p class="text-xs text-gray-400 mt-0.5">Answer all questions before submitting</p>
          </div>
          <button type="submit"
            class="bg-amber-500 hover:bg-amber-600 text-white px-8 py-3 rounded-xl font-bold text-sm transition-colors flex items-center gap-2 shadow-md">
            <i data-lucide="send" class="w-4 h-4"></i> Submit Quiz
          </button>
        </div>
      </form>

      <script>
      function validateQuiz() {
        const total = <?= count($questions) ?>;
        const answered = document.querySelectorAll('#quiz-form input[type="radio"]:checked').length;
        const groups = <?= count($questions) ?>;
        // Count unique question groups answered
        const answered_groups = new Set(
          Array.from(document.querySelectorAll('#quiz-form input[type="radio"]:checked'))
            .map(el => el.name)
        ).size;
        if (answered_groups < groups) {
          alert('Please answer all ' + groups + ' questions before submitting.\n\nYou have answered ' + answered_groups + ' so far.');
          // Scroll to first unanswered
          <?php foreach ($questions as $q): ?>
          if (!document.querySelector('input[name="answers[<?= $q['id'] ?>]"]:checked')) {
            document.getElementById('q-<?= $q['id'] ?>').scrollIntoView({behavior:'smooth',block:'center'});
            document.getElementById('q-<?= $q['id'] ?>').style.borderColor='#f59e0b';
            return false;
          }
          <?php endforeach; ?>
          return false;
        }
        return true;
      }
      </script>
      <?php endif; ?>
    </main>
    <?php require_once $_SERVER['DOCUMENT_ROOT'] . '/ByteClass/includes/footer-minimal.php'; ?>
  </div>
</div>
<?php require_once $_SERVER['DOCUMENT_ROOT'] . '/ByteClass/includes/scripts.php'; ?>