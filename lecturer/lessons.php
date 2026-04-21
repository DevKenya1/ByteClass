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

    if ($action === 'create_module') {
        $course_id = (int)($_POST['course_id'] ?? 0);
        $title     = sanitize($_POST['title'] ?? '');
        $desc      = sanitize($_POST['description'] ?? '');
        if (!$course_id || !$title) {
            $error_msg = 'Course and title required.';
        } else {
            $next_order = (int)$db->prepare("SELECT COALESCE(MAX(sort_order),0)+1 FROM modules WHERE course_id=?")
                ->execute([$course_id]) ? $db->query("SELECT COALESCE(MAX(sort_order),0)+1 FROM modules WHERE course_id=$course_id")->fetchColumn() : 1;
            $db->prepare("INSERT INTO modules (course_id, title, description, sort_order) VALUES (?,?,?,?)")
               ->execute([$course_id, $title, $desc, $next_order]);
            $success_msg = 'Module created successfully.';
        }
    }

    if ($action === 'create_lesson') {
        $module_id = (int)($_POST['module_id'] ?? 0);
        $title     = sanitize($_POST['title']     ?? '');
        $content   = sanitize($_POST['content']   ?? '');
        $video_url = sanitize($_POST['video_url'] ?? '');
        $duration  = (int)($_POST['duration_min'] ?? 0);
        if (!$module_id || !$title) {
            $error_msg = 'Module and title required.';
        } else {
            $next_order = (int)$db->query("SELECT COALESCE(MAX(sort_order),0)+1 FROM lessons WHERE module_id=$module_id")->fetchColumn();
            $db->prepare("INSERT INTO lessons (module_id, title, content, video_url, duration_min, sort_order, status, created_by)
                          VALUES (?,?,?,?,?,?,'draft',?)")
               ->execute([$module_id, $title, $content, $video_url, $duration, $next_order, $id]);
            $success_msg = 'Lesson created.';
        }
    }

    if ($action === 'publish_lesson') {
        $lid = (int)($_POST['lesson_id'] ?? 0);
        $db->prepare("UPDATE lessons SET status='published' WHERE id=?")->execute([$lid]);
        $success_msg = 'Lesson published.';
    }

    if ($action === 'unpublish_lesson') {
        $lid = (int)($_POST['lesson_id'] ?? 0);
        $db->prepare("UPDATE lessons SET status='draft' WHERE id=?")->execute([$lid]);
        $success_msg = 'Lesson set to draft.';
    }

    if ($action === 'delete_lesson') {
        $lid = (int)($_POST['lesson_id'] ?? 0);
        $db->prepare("DELETE FROM lessons WHERE id=?")->execute([$lid]);
        $success_msg = 'Lesson deleted.';
    }
}

$selected_course = (int)($_GET['course_id'] ?? 0);
$my_courses = $db->prepare("SELECT c.id, c.name FROM courses c JOIN course_lecturers cl ON cl.course_id=c.id WHERE cl.lecturer_id=? ORDER BY c.name");
$my_courses->execute([$id]); $my_courses = $my_courses->fetchAll();

if (!$selected_course && !empty($my_courses)) $selected_course = (int)$my_courses[0]['id'];

$modules = [];
if ($selected_course) {
    $mod_stmt = $db->prepare("SELECT * FROM modules WHERE course_id=? ORDER BY sort_order ASC, id ASC");
    $mod_stmt->execute([$selected_course]);
    foreach ($mod_stmt->fetchAll() as $mod) {
        $les_stmt = $db->prepare("SELECT * FROM lessons WHERE module_id=? ORDER BY sort_order ASC, id ASC");
        $les_stmt->execute([$mod['id']]);
        $mod['lessons'] = $les_stmt->fetchAll();
        $modules[] = $mod;
    }
}

$page_title = 'Lessons & Modules';
require_once $_SERVER['DOCUMENT_ROOT'] . '/ByteClass/includes/head.php';
?>
<div class="flex min-h-screen bg-gray-50">
  <?php require_once $_SERVER['DOCUMENT_ROOT'] . '/ByteClass/includes/sidebar-lecturer.php'; ?>
  <div id="main-content" class="flex-1 flex flex-col" style="margin-left:256px;">
    <?php require_once $_SERVER['DOCUMENT_ROOT'] . '/ByteClass/includes/navbar-lecturer.php'; ?>
    <main class="flex-1 p-6">
      <div class="bg-gradient-to-r from-cyan-600 to-indigo-600 rounded-2xl px-6 py-5 mb-6 flex items-center justify-between">
        <div>
          <h2 class="text-white text-xl font-bold">Lessons & Modules</h2>
          <p class="text-cyan-100 text-sm mt-0.5">Build and manage your course content</p>
        </div>
        <button onclick="document.getElementById('add-module-modal').classList.remove('hidden')"
          class="bg-white text-indigo-600 px-4 py-2 rounded-xl text-sm font-semibold hover:bg-indigo-50 flex items-center gap-2">
          <i data-lucide="plus" class="w-4 h-4"></i> Add Module
        </button>
      </div>

      <?php if ($success_msg): ?><div class="mb-4 p-4 bg-green-50 border border-green-200 rounded-xl text-sm text-green-700 flex gap-3"><i data-lucide="check-circle" class="w-5 h-5 flex-shrink-0"></i><?= htmlspecialchars($success_msg) ?></div><?php endif; ?>
      <?php if ($error_msg): ?><div class="mb-4 p-4 bg-red-50 border border-red-200 rounded-xl text-sm text-red-700 flex gap-3"><i data-lucide="alert-circle" class="w-5 h-5 flex-shrink-0"></i><?= htmlspecialchars($error_msg) ?></div><?php endif; ?>

      <!-- Course selector -->
      <form method="GET" class="bg-white rounded-2xl border border-gray-100 p-4 mb-5 flex gap-3 items-center">
        <label class="text-sm font-medium text-gray-600 whitespace-nowrap">Viewing course:</label>
        <select name="course_id" class="flex-1 px-4 py-2.5 border border-gray-200 rounded-xl text-sm focus:outline-none focus:ring-2 focus:ring-indigo-500" onchange="this.form.submit()">
          <?php if (empty($my_courses)): ?>
          <option>No courses assigned</option>
          <?php else: foreach ($my_courses as $mc): ?>
          <option value="<?= $mc['id'] ?>" <?= $selected_course===(int)$mc['id']?'selected':'' ?>><?= htmlspecialchars($mc['name']) ?></option>
          <?php endforeach; endif; ?>
        </select>
      </form>

      <?php if (empty($my_courses)): ?>
      <div class="bg-white rounded-2xl border border-gray-100 p-12 text-center">
        <i data-lucide="book-open" class="w-10 h-10 text-gray-300 mx-auto mb-3"></i>
        <p class="text-gray-500">No courses assigned to you yet. Contact your administrator.</p>
      </div>
      <?php elseif (empty($modules)): ?>
      <div class="bg-white rounded-2xl border border-gray-100 p-12 text-center">
        <i data-lucide="layers" class="w-10 h-10 text-gray-300 mx-auto mb-3"></i>
        <p class="text-gray-500 font-medium">No modules yet</p>
        <p class="text-gray-400 text-sm mt-1 mb-4">Create your first module to start building this course</p>
        <button onclick="document.getElementById('add-module-modal').classList.remove('hidden')"
          class="inline-flex items-center gap-2 bg-indigo-600 text-white px-5 py-2.5 rounded-xl text-sm font-semibold hover:bg-indigo-700">
          <i data-lucide="plus" class="w-4 h-4"></i> Add First Module
        </button>
      </div>
      <?php else: ?>
      <div class="space-y-4">
        <?php foreach ($modules as $idx => $mod): ?>
        <div class="bg-white rounded-2xl border border-gray-100 overflow-hidden shadow-sm">
          <div class="px-5 py-4 bg-gradient-to-r from-gray-50 to-indigo-50/30 border-b border-gray-100 flex items-center justify-between">
            <div class="flex items-center gap-3">
              <div class="w-8 h-8 bg-indigo-600 rounded-xl flex items-center justify-center flex-shrink-0">
                <span class="text-white font-bold text-sm"><?= $idx+1 ?></span>
              </div>
              <div>
                <h3 class="font-semibold text-gray-900"><?= htmlspecialchars($mod['title']) ?></h3>
                <?php if ($mod['description']): ?>
                <p class="text-xs text-gray-400 mt-0.5"><?= htmlspecialchars($mod['description']) ?></p>
                <?php endif; ?>
              </div>
              <span class="text-xs bg-indigo-100 text-indigo-700 px-2.5 py-1 rounded-full font-medium">
                <?= count($mod['lessons']) ?> lesson<?= count($mod['lessons'])!=1?'s':'' ?>
              </span>
            </div>
            <button onclick="showAddLesson(<?= $mod['id'] ?>, '<?= htmlspecialchars(addslashes($mod['title'])) ?>')"
              class="text-xs bg-indigo-600 text-white px-3 py-1.5 rounded-lg hover:bg-indigo-700 flex items-center gap-1 transition-colors">
              <i data-lucide="plus" class="w-3 h-3"></i> Add Lesson
            </button>
          </div>

          <?php if (empty($mod['lessons'])): ?>
          <div class="px-5 py-8 text-center text-gray-400 text-sm">
            No lessons yet.
            <button onclick="showAddLesson(<?= $mod['id'] ?>, '<?= htmlspecialchars(addslashes($mod['title'])) ?>')"
              class="text-indigo-600 hover:underline ml-1">Add the first lesson →</button>
          </div>
          <?php else: ?>
          <div class="divide-y divide-gray-50">
            <?php foreach ($mod['lessons'] as $lesIdx => $les): ?>
            <div class="px-5 py-4 flex items-center gap-4 hover:bg-gray-50 transition-colors">
              <div class="w-7 h-7 bg-gray-100 rounded-lg flex items-center justify-center flex-shrink-0">
                <span class="text-gray-500 font-medium text-xs"><?= $lesIdx+1 ?></span>
              </div>
              <i data-lucide="<?= $les['video_url'] ? 'play-circle' : 'file-text' ?>"
                 class="w-4 h-4 <?= $les['video_url'] ? 'text-blue-500' : 'text-gray-400' ?> flex-shrink-0"></i>
              <div class="flex-1 min-w-0">
                <p class="text-sm font-medium text-gray-900 truncate"><?= htmlspecialchars($les['title']) ?></p>
                <div class="flex items-center gap-3 mt-0.5 text-xs text-gray-400">
                  <?php if ($les['duration_min']): ?><span><?= $les['duration_min'] ?> min</span><?php endif; ?>
                  <?php if ($les['video_url']): ?><span class="text-blue-500 flex items-center gap-1"><i data-lucide="youtube" class="w-3 h-3"></i>Has video</span><?php endif; ?>
                  <?php if ($les['content']): ?><span class="flex items-center gap-1"><i data-lucide="align-left" class="w-3 h-3"></i>Has notes</span><?php endif; ?>
                </div>
              </div>
              <span class="text-xs px-2.5 py-1 rounded-full font-medium flex-shrink-0
                <?= $les['status']==='published' ? 'bg-green-100 text-green-700' : 'bg-amber-100 text-amber-700' ?>">
                <?= ucfirst($les['status']) ?>
              </span>
              <div class="flex items-center gap-1 flex-shrink-0">
                <form method="POST" class="inline">
                  <input type="hidden" name="action" value="<?= $les['status']==='published' ? 'unpublish_lesson' : 'publish_lesson' ?>">
                  <input type="hidden" name="lesson_id" value="<?= $les['id'] ?>">
                  <button type="submit"
                    class="text-xs px-2.5 py-1.5 border border-gray-200 rounded-lg text-gray-600 hover:bg-gray-100 transition-colors">
                    <?= $les['status']==='published' ? 'Unpublish' : 'Publish' ?>
                  </button>
                </form>
                <form method="POST" class="inline" onsubmit="return confirm('Delete this lesson?')">
                  <input type="hidden" name="action" value="delete_lesson">
                  <input type="hidden" name="lesson_id" value="<?= $les['id'] ?>">
                  <button type="submit" class="p-1.5 text-gray-300 hover:text-red-500 hover:bg-red-50 rounded-lg transition-colors">
                    <i data-lucide="trash-2" class="w-3.5 h-3.5"></i>
                  </button>
                </form>
              </div>
            </div>
            <?php endforeach; ?>
          </div>
          <?php endif; ?>
        </div>
        <?php endforeach; ?>
      </div>
      <?php endif; ?>
    </main>
    <?php require_once $_SERVER['DOCUMENT_ROOT'] . '/ByteClass/includes/footer-minimal.php'; ?>
  </div>
</div>

<!-- Add Module Modal -->
<div id="add-module-modal" class="hidden fixed inset-0 z-50 flex items-center justify-center p-4" style="background:rgba(0,0,0,0.5);">
  <div class="bg-white rounded-2xl w-full max-w-md shadow-xl">
    <div class="flex items-center justify-between px-6 py-4 border-b border-gray-100">
      <h3 class="font-semibold text-gray-900 flex items-center gap-2">
        <i data-lucide="layers" class="w-5 h-5 text-indigo-600"></i> Add New Module
      </h3>
      <button onclick="document.getElementById('add-module-modal').classList.add('hidden')" class="text-gray-400 hover:text-gray-600"><i data-lucide="x" class="w-5 h-5"></i></button>
    </div>
    <form method="POST" class="p-6 space-y-4">
      <input type="hidden" name="action" value="create_module">
      <input type="hidden" name="course_id" value="<?= $selected_course ?>">
      <div>
        <label class="block text-sm font-medium text-gray-700 mb-1.5">Module title *</label>
        <input type="text" name="title" required placeholder="e.g. Module 1: Introduction to Computer Basics"
          class="w-full px-4 py-3 border border-gray-200 rounded-xl text-sm focus:outline-none focus:ring-2 focus:ring-indigo-500" />
      </div>
      <div>
        <label class="block text-sm font-medium text-gray-700 mb-1.5">Description (optional)</label>
        <textarea name="description" rows="2" placeholder="Brief overview of what this module covers..."
          class="w-full px-4 py-3 border border-gray-200 rounded-xl text-sm focus:outline-none focus:ring-2 focus:ring-indigo-500 resize-none"></textarea>
      </div>
      <div class="flex gap-3 pt-2">
        <button type="button" onclick="document.getElementById('add-module-modal').classList.add('hidden')"
          class="flex-1 px-4 py-3 border border-gray-200 rounded-xl text-sm text-gray-700 hover:bg-gray-50">Cancel</button>
        <button type="submit" class="flex-1 bg-indigo-600 text-white px-4 py-3 rounded-xl text-sm font-semibold hover:bg-indigo-700 flex items-center justify-center gap-2">
          <i data-lucide="plus" class="w-4 h-4"></i> Create Module
        </button>
      </div>
    </form>
  </div>
</div>

<!-- Add Lesson Modal -->
<div id="add-lesson-modal" class="hidden fixed inset-0 z-50 flex items-center justify-center p-4" style="background:rgba(0,0,0,0.5);">
  <div class="bg-white rounded-2xl w-full max-w-lg shadow-xl max-h-screen overflow-y-auto">
    <div class="flex items-center justify-between px-6 py-4 border-b border-gray-100 sticky top-0 bg-white z-10">
      <h3 class="font-semibold text-gray-900">
        Add Lesson — <span id="module-name-display" class="text-indigo-600"></span>
      </h3>
      <button onclick="document.getElementById('add-lesson-modal').classList.add('hidden')" class="text-gray-400 hover:text-gray-600"><i data-lucide="x" class="w-5 h-5"></i></button>
    </div>
    <form method="POST" class="p-6 space-y-4">
      <input type="hidden" name="action" value="create_lesson">
      <input type="hidden" name="module_id" id="lesson-module-id">
      <div>
        <label class="block text-sm font-medium text-gray-700 mb-1.5">Lesson title *</label>
        <input type="text" name="title" required placeholder="e.g. What is a Firewall?"
          class="w-full px-4 py-3 border border-gray-200 rounded-xl text-sm focus:outline-none focus:ring-2 focus:ring-indigo-500" />
      </div>
      <div>
        <label class="block text-sm font-medium text-gray-700 mb-1.5">Video URL <span class="text-gray-400 font-normal">(YouTube embed)</span></label>
        <input type="url" name="video_url" placeholder="https://www.youtube.com/embed/VIDEO_ID"
          class="w-full px-4 py-3 border border-gray-200 rounded-xl text-sm focus:outline-none focus:ring-2 focus:ring-indigo-500" />
        <p class="text-xs text-gray-400 mt-1">Use the embed URL: youtube.com/embed/VIDEO_ID (not the regular watch URL)</p>
      </div>
      <div>
        <label class="block text-sm font-medium text-gray-700 mb-1.5">Lesson content / notes</label>
        <textarea name="content" rows="5" placeholder="Write lesson notes, key points, code examples..."
          class="w-full px-4 py-3 border border-gray-200 rounded-xl text-sm focus:outline-none focus:ring-2 focus:ring-indigo-500 resize-none"></textarea>
      </div>
      <div>
        <label class="block text-sm font-medium text-gray-700 mb-1.5">Duration (minutes)</label>
        <input type="number" name="duration_min" min="1" max="300" placeholder="e.g. 20"
          class="w-full px-4 py-3 border border-gray-200 rounded-xl text-sm focus:outline-none focus:ring-2 focus:ring-indigo-500" />
      </div>
      <div class="flex gap-3 pt-2">
        <button type="button" onclick="document.getElementById('add-lesson-modal').classList.add('hidden')"
          class="flex-1 px-4 py-3 border border-gray-200 rounded-xl text-sm text-gray-700 hover:bg-gray-50">Cancel</button>
        <button type="submit" class="flex-1 bg-indigo-600 text-white px-4 py-3 rounded-xl text-sm font-semibold hover:bg-indigo-700 flex items-center justify-center gap-2">
          <i data-lucide="book-open" class="w-4 h-4"></i> Add Lesson
        </button>
      </div>
    </form>
  </div>
</div>

<?php require_once $_SERVER['DOCUMENT_ROOT'] . '/ByteClass/includes/scripts.php'; ?>
<script>
function showAddLesson(moduleId, moduleName) {
  document.getElementById('lesson-module-id').value = moduleId;
  document.getElementById('module-name-display').textContent = moduleName;
  document.getElementById('add-lesson-modal').classList.remove('hidden');
  lucide.createIcons();
}
</script>
