<?php
$required_role = 'lecturer';
require_once $_SERVER['DOCUMENT_ROOT'] . '/ByteClass/includes/auth-check.php';
require_once $_SERVER['DOCUMENT_ROOT'] . '/ByteClass/config/constants.php';
require_once $_SERVER['DOCUMENT_ROOT'] . '/ByteClass/config/database.php';
require_once $_SERVER['DOCUMENT_ROOT'] . '/ByteClass/helpers/response.php';
$db = Database::getInstance()->getConnection();
$id = (int)$_SESSION['user_id'];
$success_msg = $error_msg = '';

// ── Handle POST actions ───────────────────────────────────────────────────────
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $action = $_POST['action'] ?? '';

    // Create quiz for a lesson
    if ($action === 'create_quiz') {
        $lesson_id  = (int)($_POST['lesson_id']  ?? 0);
        $title      = sanitize($_POST['title']   ?? 'Lesson Quiz');
        $pass_mark  = max(1, min(100, (int)($_POST['pass_mark'] ?? 70)));
        $desc       = sanitize($_POST['description'] ?? '');
        if (!$lesson_id) { $error_msg = 'Select a lesson.'; }
        else {
            $exists = $db->prepare("SELECT id FROM quizzes WHERE lesson_id=? LIMIT 1");
            $exists->execute([$lesson_id]);
            if ($exists->fetch()) { $error_msg = 'This lesson already has a quiz. Edit it instead.'; }
            else {
                $db->prepare("INSERT INTO quizzes (lesson_id, title, description, pass_mark) VALUES (?,?,?,?)")
                   ->execute([$lesson_id, $title, $desc, $pass_mark]);
                $success_msg = "Quiz created! Now add questions.";
            }
        }
    }

    // Add question to quiz
    if ($action === 'add_question') {
        $quiz_id    = (int)($_POST['quiz_id']   ?? 0);
        $question   = sanitize($_POST['question'] ?? '');
        $options    = $_POST['options']    ?? [];
        $correct    = (int)($_POST['correct']  ?? 0); // index 0-3
        if (!$quiz_id || !$question || count($options) < 2) {
            $error_msg = 'Quiz, question, and at least 2 options required.';
        } else {
            $next_order = (int)$db->query("SELECT COALESCE(MAX(sort_order),0)+1 FROM quiz_questions WHERE quiz_id=$quiz_id")->fetchColumn();
            $db->prepare("INSERT INTO quiz_questions (quiz_id, question, sort_order) VALUES (?,?,?)")
               ->execute([$quiz_id, $question, $next_order]);
            $q_id = (int)$db->lastInsertId();
            foreach ($options as $i => $opt) {
                $opt = sanitize($opt);
                if (!$opt) continue;
                $db->prepare("INSERT INTO quiz_options (question_id, option_text, is_correct, sort_order) VALUES (?,?,?,?)")
                   ->execute([$q_id, $opt, ($i === $correct) ? 1 : 0, $i+1]);
            }
            $success_msg = 'Question added!';
        }
    }

    // Delete question
    if ($action === 'delete_question') {
        $qid = (int)($_POST['question_id'] ?? 0);
        $db->prepare("DELETE FROM quiz_questions WHERE id=?")->execute([$qid]);
        $success_msg = 'Question deleted.';
    }

    // Delete entire quiz
    if ($action === 'delete_quiz') {
        $qzid = (int)($_POST['quiz_id'] ?? 0);
        $db->prepare("DELETE FROM quizzes WHERE id=?")->execute([$qzid]);
        $success_msg = 'Quiz deleted.';
    }

    // Update quiz settings
    if ($action === 'update_quiz') {
        $qzid      = (int)($_POST['quiz_id']  ?? 0);
        $title     = sanitize($_POST['title'] ?? '');
        $pass_mark = max(1, min(100, (int)($_POST['pass_mark'] ?? 70)));
        $db->prepare("UPDATE quizzes SET title=?, pass_mark=? WHERE id=?")->execute([$title, $pass_mark, $qzid]);
        $success_msg = 'Quiz updated.';
    }
}

// ── Load data ────────────────────────────────────────────────────────────────
$selected_course = (int)($_GET['course_id'] ?? 0);
$my_courses = $db->prepare("SELECT c.id, c.name FROM courses c JOIN course_lecturers cl ON cl.course_id=c.id WHERE cl.lecturer_id=? ORDER BY c.name");
$my_courses->execute([$id]); $my_courses = $my_courses->fetchAll();
if (!$selected_course && !empty($my_courses)) $selected_course = (int)$my_courses[0]['id'];

// Get all lessons for selected course with their quizzes
$lessons_with_quizzes = [];
if ($selected_course) {
    $mods = $db->prepare("SELECT * FROM modules WHERE course_id=? ORDER BY sort_order ASC, id ASC");
    $mods->execute([$selected_course]);
    foreach ($mods->fetchAll() as $mod) {
        $les = $db->prepare("SELECT l.*, q.id AS quiz_id, q.title AS quiz_title, q.pass_mark,
            (SELECT COUNT(*) FROM quiz_questions WHERE quiz_id=q.id) AS question_count,
            (SELECT COUNT(*) FROM quiz_attempts WHERE quiz_id=q.id) AS attempt_count
            FROM lessons l LEFT JOIN quizzes q ON q.lesson_id=l.id
            WHERE l.module_id=? AND l.status='published' ORDER BY l.sort_order ASC, l.id ASC");
        $les->execute([$mod['id']]);
        $mod['lessons'] = $les->fetchAll();
        $lessons_with_quizzes[] = $mod;
    }
}

// Load questions for selected quiz
$selected_quiz = (int)($_GET['quiz_id'] ?? 0);
$quiz_data     = null;
$questions     = [];
if ($selected_quiz) {
    $qz = $db->prepare("SELECT q.*, l.title AS lesson_title FROM quizzes q JOIN lessons l ON l.id=q.lesson_id WHERE q.id=?");
    $qz->execute([$selected_quiz]); $quiz_data = $qz->fetch();
    if ($quiz_data) {
        $qs = $db->prepare("SELECT qq.*, GROUP_CONCAT(qo.id ORDER BY qo.sort_order SEPARATOR '|') AS opt_ids,
            GROUP_CONCAT(qo.option_text ORDER BY qo.sort_order SEPARATOR '|||') AS opt_texts,
            GROUP_CONCAT(qo.is_correct ORDER BY qo.sort_order SEPARATOR '|') AS opt_correct
            FROM quiz_questions qq
            LEFT JOIN quiz_options qo ON qo.question_id=qq.id
            WHERE qq.quiz_id=?
            GROUP BY qq.id ORDER BY qq.sort_order ASC");
        $qs->execute([$selected_quiz]); $questions = $qs->fetchAll();
    }
}

$page_title = 'Quiz Management';
require_once $_SERVER['DOCUMENT_ROOT'] . '/ByteClass/includes/head.php';
?>
<div class="flex min-h-screen bg-gray-50">
  <?php require_once $_SERVER['DOCUMENT_ROOT'] . '/ByteClass/includes/sidebar-lecturer.php'; ?>
  <div id="main-content" class="flex-1 flex flex-col" style="margin-left:256px;">
    <?php require_once $_SERVER['DOCUMENT_ROOT'] . '/ByteClass/includes/navbar-lecturer.php'; ?>
    <main class="flex-1 p-6">

      <!-- Banner -->
      <div class="bg-gradient-to-r from-amber-500 to-orange-500 rounded-2xl px-6 py-5 mb-6 flex items-center justify-between">
        <div>
          <h2 class="text-white text-xl font-bold flex items-center gap-2">
            <i data-lucide="help-circle" class="w-6 h-6"></i> Quiz Management
          </h2>
          <p class="text-amber-100 text-sm mt-0.5">Create and manage quizzes for your lessons</p>
        </div>
        <a href="<?= APP_URL ?>/lecturer/lessons.php<?= $selected_course ? '?course_id='.$selected_course : '' ?>"
           class="bg-white text-amber-600 px-4 py-2 rounded-xl text-sm font-semibold hover:bg-amber-50 flex items-center gap-2">
          <i data-lucide="layers" class="w-4 h-4"></i> Manage Lessons
        </a>
      </div>

      <?php if ($success_msg): ?>
      <div class="mb-4 p-4 bg-green-50 border border-green-200 rounded-xl text-sm text-green-700 flex gap-3">
        <i data-lucide="check-circle" class="w-5 h-5 flex-shrink-0"></i><?= htmlspecialchars($success_msg) ?>
      </div>
      <?php endif; ?>
      <?php if ($error_msg): ?>
      <div class="mb-4 p-4 bg-red-50 border border-red-200 rounded-xl text-sm text-red-700 flex gap-3">
        <i data-lucide="alert-circle" class="w-5 h-5 flex-shrink-0"></i><?= htmlspecialchars($error_msg) ?>
      </div>
      <?php endif; ?>

      <div class="grid grid-cols-1 lg:grid-cols-2 gap-6">

        <!-- LEFT: Lessons + quiz status -->
        <div>
          <!-- Course picker -->
          <form method="GET" class="bg-white rounded-2xl border border-gray-100 p-4 mb-4 flex gap-3 items-center">
            <label class="text-sm font-medium text-gray-600 whitespace-nowrap">Course:</label>
            <select name="course_id" class="flex-1 px-4 py-2.5 border border-gray-200 rounded-xl text-sm focus:outline-none focus:ring-2 focus:ring-amber-500" onchange="this.form.submit()">
              <?php foreach ($my_courses as $mc): ?>
              <option value="<?= $mc['id'] ?>" <?= $selected_course===(int)$mc['id']?'selected':'' ?>><?= htmlspecialchars($mc['name']) ?></option>
              <?php endforeach; ?>
            </select>
          </form>

          <div class="space-y-3">
            <?php if (empty($lessons_with_quizzes)): ?>
            <div class="bg-white rounded-2xl border border-gray-100 p-10 text-center">
              <i data-lucide="layers" class="w-10 h-10 text-gray-300 mx-auto mb-3"></i>
              <p class="text-gray-400 text-sm">No published lessons yet. Publish lessons first.</p>
            </div>
            <?php else: foreach ($lessons_with_quizzes as $modIdx => $mod): ?>
            <div class="bg-white rounded-2xl border border-gray-100 overflow-hidden">
              <div class="px-4 py-3 bg-gray-50 border-b border-gray-100 flex items-center gap-2">
                <span class="w-6 h-6 bg-amber-500 rounded-lg flex items-center justify-center text-white text-xs font-bold flex-shrink-0"><?= $modIdx+1 ?></span>
                <h3 class="font-semibold text-gray-800 text-sm"><?= htmlspecialchars($mod['title']) ?></h3>
              </div>
              <?php if (empty($mod['lessons'])): ?>
              <div class="px-4 py-4 text-sm text-gray-400 text-center">No published lessons</div>
              <?php else: foreach ($mod['lessons'] as $lesIdx => $les): ?>
              <div class="px-4 py-3 flex items-center gap-3 border-b border-gray-50 last:border-0 hover:bg-gray-50 transition-colors">
                <div class="flex-1 min-w-0">
                  <p class="text-sm font-medium text-gray-900 truncate"><?= htmlspecialchars($les['title']) ?></p>
                  <?php if ($les['quiz_id']): ?>
                  <p class="text-xs text-amber-600 font-medium mt-0.5 flex items-center gap-1">
                    <i data-lucide="check-circle" class="w-3 h-3"></i>
                    Quiz: <?= $les['question_count'] ?> question<?= $les['question_count']!=1?'s':'' ?>
                    · <?= $les['attempt_count'] ?> attempt<?= $les['attempt_count']!=1?'s':'' ?>
                    · Pass: <?= $les['pass_mark'] ?>%
                  </p>
                  <?php else: ?>
                  <p class="text-xs text-gray-400 mt-0.5 flex items-center gap-1">
                    <i data-lucide="circle" class="w-3 h-3"></i> No quiz
                  </p>
                  <?php endif; ?>
                </div>
                <?php if ($les['quiz_id']): ?>
                <div class="flex items-center gap-1.5 flex-shrink-0">
                  <a href="?course_id=<?= $selected_course ?>&quiz_id=<?= $les['quiz_id'] ?>"
                     class="text-xs px-2.5 py-1.5 bg-amber-100 text-amber-700 rounded-lg hover:bg-amber-200 transition-colors flex items-center gap-1 font-medium">
                    <i data-lucide="edit" class="w-3 h-3"></i> Edit
                  </a>
                  <form method="POST" class="inline" onsubmit="return confirm('Delete this entire quiz?')">
                    <input type="hidden" name="action" value="delete_quiz">
                    <input type="hidden" name="quiz_id" value="<?= $les['quiz_id'] ?>">
                    <button type="submit" class="p-1.5 text-gray-300 hover:text-red-500 hover:bg-red-50 rounded-lg transition-colors">
                      <i data-lucide="trash-2" class="w-3.5 h-3.5"></i>
                    </button>
                  </form>
                </div>
                <?php else: ?>
                <button onclick="showCreateQuiz(<?= $les['id'] ?>, '<?= htmlspecialchars(addslashes($les['title'])) ?>')"
                  class="text-xs px-2.5 py-1.5 bg-indigo-100 text-indigo-700 rounded-lg hover:bg-indigo-200 transition-colors flex items-center gap-1 font-medium flex-shrink-0">
                  <i data-lucide="plus" class="w-3 h-3"></i> Add Quiz
                </button>
                <?php endif; ?>
              </div>
              <?php endforeach; endif; ?>
            </div>
            <?php endforeach; endif; ?>
          </div>
        </div>

        <!-- RIGHT: Quiz editor -->
        <div>
          <?php if ($quiz_data): ?>
          <!-- Quiz header -->
          <div class="bg-white rounded-2xl border border-gray-100 p-5 mb-4">
            <div class="flex items-start justify-between gap-4 mb-4">
              <div>
                <h3 class="font-bold text-gray-900">Editing: <?= htmlspecialchars($quiz_data['quiz_title'] ?? $quiz_data['title']) ?></h3>
                <p class="text-xs text-gray-400 mt-0.5">Lesson: <?= htmlspecialchars($quiz_data['lesson_title']) ?></p>
              </div>
              <span class="bg-amber-100 text-amber-700 text-xs font-bold px-2.5 py-1 rounded-full flex items-center gap-1">
                <i data-lucide="help-circle" class="w-3 h-3"></i> <?= count($questions) ?> questions
              </span>
            </div>
            <!-- Quick settings -->
            <form method="POST" class="flex items-end gap-3">
              <input type="hidden" name="action" value="update_quiz">
              <input type="hidden" name="quiz_id" value="<?= $quiz_data['id'] ?>">
              <div class="flex-1">
                <label class="block text-xs font-medium text-gray-600 mb-1">Quiz title</label>
                <input type="text" name="title" value="<?= htmlspecialchars($quiz_data['title']) ?>"
                  class="w-full px-3 py-2 border border-gray-200 rounded-xl text-sm focus:outline-none focus:ring-2 focus:ring-amber-500" />
              </div>
              <div class="w-28">
                <label class="block text-xs font-medium text-gray-600 mb-1">Pass mark %</label>
                <input type="number" name="pass_mark" min="1" max="100" value="<?= $quiz_data['pass_mark'] ?>"
                  class="w-full px-3 py-2 border border-gray-200 rounded-xl text-sm focus:outline-none focus:ring-2 focus:ring-amber-500" />
              </div>
              <button type="submit" class="bg-amber-500 text-white px-4 py-2 rounded-xl text-sm font-semibold hover:bg-amber-600 transition-colors whitespace-nowrap">
                Save
              </button>
            </form>
          </div>

          <!-- Existing questions -->
          <?php if (!empty($questions)): ?>
          <div class="space-y-3 mb-4">
            <?php foreach ($questions as $qi => $q):
              $opts   = explode('|||', $q['opt_texts'] ?? '');
              $corrects = explode('|', $q['opt_correct'] ?? '');
            ?>
            <div class="bg-white rounded-2xl border border-gray-100 p-4">
              <div class="flex items-start justify-between gap-3 mb-3">
                <div class="flex items-center gap-2 flex-1 min-w-0">
                  <span class="w-7 h-7 bg-indigo-600 rounded-xl flex items-center justify-center text-white text-xs font-bold flex-shrink-0"><?= $qi+1 ?></span>
                  <p class="text-sm font-semibold text-gray-900 leading-snug"><?= htmlspecialchars($q['question']) ?></p>
                </div>
                <form method="POST" class="inline flex-shrink-0" onsubmit="return confirm('Delete this question?')">
                  <input type="hidden" name="action" value="delete_question">
                  <input type="hidden" name="question_id" value="<?= $q['id'] ?>">
                  <input type="hidden" name="quiz_id" value="<?= $selected_quiz ?>">
                  <button type="submit" class="p-1.5 text-gray-300 hover:text-red-500 hover:bg-red-50 rounded-lg transition-colors">
                    <i data-lucide="trash-2" class="w-3.5 h-3.5"></i>
                  </button>
                </form>
              </div>
              <div class="grid grid-cols-2 gap-2">
                <?php foreach ($opts as $oi => $opt): if (!trim($opt)) continue; ?>
                <div class="flex items-center gap-2 px-3 py-2 rounded-xl text-xs
                  <?= ($corrects[$oi] ?? 0) ? 'bg-green-50 border border-green-200 text-green-800' : 'bg-gray-50 border border-gray-200 text-gray-600' ?>">
                  <?php if ($corrects[$oi] ?? 0): ?>
                  <i data-lucide="check-circle" class="w-3.5 h-3.5 text-green-600 flex-shrink-0"></i>
                  <?php else: ?>
                  <i data-lucide="circle" class="w-3.5 h-3.5 text-gray-300 flex-shrink-0"></i>
                  <?php endif; ?>
                  <span><?= htmlspecialchars($opt) ?></span>
                </div>
                <?php endforeach; ?>
              </div>
            </div>
            <?php endforeach; ?>
          </div>
          <?php endif; ?>

          <!-- Add question form -->
          <div class="bg-white rounded-2xl border border-gray-100 p-5">
            <h4 class="font-semibold text-gray-900 mb-4 flex items-center gap-2">
              <i data-lucide="plus-circle" class="w-5 h-5 text-amber-500"></i> Add New Question
            </h4>
            <form method="POST" class="space-y-4">
              <input type="hidden" name="action" value="add_question">
              <input type="hidden" name="quiz_id" value="<?= $selected_quiz ?>">
              <div>
                <label class="block text-sm font-medium text-gray-700 mb-1.5">Question *</label>
                <textarea name="question" required rows="2"
                  placeholder="e.g. What does DNS stand for?"
                  class="w-full px-4 py-3 border border-gray-200 rounded-xl text-sm focus:outline-none focus:ring-2 focus:ring-amber-500 resize-none"></textarea>
              </div>
              <div>
                <label class="block text-sm font-medium text-gray-700 mb-2">Answer options *</label>
                <div class="space-y-2">
                  <?php foreach (['A','B','C','D'] as $oi => $label): ?>
                  <div class="flex items-center gap-3">
                    <label class="flex items-center gap-2 cursor-pointer">
                      <input type="radio" name="correct" value="<?= $oi ?>" <?= $oi===0?'checked':'' ?> required
                        class="w-4 h-4 text-amber-600 accent-amber-600 cursor-pointer" />
                      <span class="w-7 h-7 bg-gray-100 rounded-lg flex items-center justify-center text-xs font-bold text-gray-600 flex-shrink-0"><?= $label ?></span>
                    </label>
                    <input type="text" name="options[]"
                      placeholder="Option <?= $label ?>..."
                      class="flex-1 px-3 py-2.5 border border-gray-200 rounded-xl text-sm focus:outline-none focus:ring-2 focus:ring-amber-500" />
                  </div>
                  <?php endforeach; ?>
                </div>
                <p class="text-xs text-gray-400 mt-2 flex items-center gap-1">
                  <i data-lucide="info" class="w-3 h-3"></i>
                  Select the radio button next to the correct answer
                </p>
              </div>
              <button type="submit"
                class="w-full bg-amber-500 text-white py-3 rounded-xl text-sm font-bold hover:bg-amber-600 transition-colors flex items-center justify-center gap-2 shadow-sm">
                <i data-lucide="plus" class="w-4 h-4"></i> Add Question
              </button>
            </form>
          </div>

          <?php else: ?>
          <div class="bg-white rounded-2xl border border-gray-100 p-12 text-center">
            <div class="w-16 h-16 bg-amber-100 rounded-2xl flex items-center justify-center mx-auto mb-4">
              <i data-lucide="help-circle" class="w-8 h-8 text-amber-500"></i>
            </div>
            <h3 class="font-bold text-gray-900 mb-2">No quiz selected</h3>
            <p class="text-gray-400 text-sm">Click <strong>Add Quiz</strong> on a lesson, or <strong>Edit</strong> an existing quiz to manage questions.</p>
          </div>
          <?php endif; ?>
        </div>

      </div>
    </main>
    <?php require_once $_SERVER['DOCUMENT_ROOT'] . '/ByteClass/includes/footer-minimal.php'; ?>
  </div>
</div>

<!-- Create Quiz Modal -->
<div id="create-quiz-modal" class="hidden fixed inset-0 z-50 flex items-center justify-center p-4" style="background:rgba(0,0,0,0.5);">
  <div class="bg-white rounded-2xl w-full max-w-md shadow-xl">
    <div class="flex items-center justify-between px-6 py-4 border-b border-gray-100">
      <h3 class="font-semibold text-gray-900 flex items-center gap-2">
        <i data-lucide="plus-circle" class="w-5 h-5 text-amber-500"></i>
        Create Quiz — <span id="quiz-lesson-name" class="text-amber-600 text-sm font-normal truncate max-w-32"></span>
      </h3>
      <button onclick="document.getElementById('create-quiz-modal').classList.add('hidden')"
        class="text-gray-400 hover:text-gray-600"><i data-lucide="x" class="w-5 h-5"></i></button>
    </div>
    <form method="POST" class="p-6 space-y-4">
      <input type="hidden" name="action" value="create_quiz">
      <input type="hidden" name="lesson_id" id="quiz-lesson-id">
      <div>
        <label class="block text-sm font-medium text-gray-700 mb-1.5">Quiz title</label>
        <input type="text" name="title" value="Lesson Quiz" required
          class="w-full px-4 py-3 border border-gray-200 rounded-xl text-sm focus:outline-none focus:ring-2 focus:ring-amber-500" />
      </div>
      <div>
        <label class="block text-sm font-medium text-gray-700 mb-1.5">Pass mark (%)</label>
        <input type="number" name="pass_mark" value="70" min="1" max="100"
          class="w-full px-4 py-3 border border-gray-200 rounded-xl text-sm focus:outline-none focus:ring-2 focus:ring-amber-500" />
        <p class="text-xs text-gray-400 mt-1">Students must score this % or above to pass</p>
      </div>
      <div class="flex gap-3 pt-2">
        <button type="button" onclick="document.getElementById('create-quiz-modal').classList.add('hidden')"
          class="flex-1 px-4 py-3 border border-gray-200 rounded-xl text-sm text-gray-700 hover:bg-gray-50">Cancel</button>
        <button type="submit"
          class="flex-1 bg-amber-500 text-white px-4 py-3 rounded-xl text-sm font-semibold hover:bg-amber-600 flex items-center justify-center gap-2">
          <i data-lucide="check" class="w-4 h-4"></i> Create Quiz
        </button>
      </div>
    </form>
  </div>
</div>

<?php require_once $_SERVER['DOCUMENT_ROOT'] . '/ByteClass/includes/scripts.php'; ?>
<script>
function showCreateQuiz(lessonId, lessonName) {
  document.getElementById('quiz-lesson-id').value = lessonId;
  document.getElementById('quiz-lesson-name').textContent = lessonName;
  document.getElementById('create-quiz-modal').classList.remove('hidden');
  lucide.createIcons();
}
</script>