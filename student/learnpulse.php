<?php
$required_role = 'student';
require_once $_SERVER['DOCUMENT_ROOT'] . '/ByteClass/includes/auth-check.php';
require_once $_SERVER['DOCUMENT_ROOT'] . '/ByteClass/config/constants.php';
require_once $_SERVER['DOCUMENT_ROOT'] . '/ByteClass/config/database.php';
require_once $_SERVER['DOCUMENT_ROOT'] . '/ByteClass/helpers/response.php';

$db = Database::getInstance()->getConnection();

// Check if AI is enabled
$ai_enabled = $db->query("SELECT setting_value FROM system_settings WHERE setting_key='learnpulse_enabled'")->fetchColumn();

$page_title = 'LearnPulse AI';
require_once $_SERVER['DOCUMENT_ROOT'] . '/ByteClass/includes/head.php';
?>
<div class="flex min-h-screen bg-gray-50">
  <?php require_once $_SERVER['DOCUMENT_ROOT'] . '/ByteClass/includes/sidebar-student.php'; ?>
  <div id="main-content" class="flex-1 flex flex-col" style="margin-left:256px;">
    <?php require_once $_SERVER['DOCUMENT_ROOT'] . '/ByteClass/includes/navbar-student.php'; ?>
    <main class="flex-1 p-6 flex flex-col">
      <div class="bg-gradient-to-r from-indigo-600 to-cyan-500 rounded-2xl px-6 py-5 mb-6 flex items-center gap-4">
        <div class="w-12 h-12 bg-white bg-opacity-20 rounded-2xl flex items-center justify-center">
          <i data-lucide="zap" class="w-6 h-6 text-white"></i>
        </div>
        <div>
          <h2 class="text-white text-xl font-bold">LearnPulse AI</h2>
          <p class="text-indigo-100 text-sm mt-0.5">Your personal AI tutor — ask anything about your courses</p>
        </div>
      </div>

      <?php if (!$ai_enabled || $ai_enabled === '0'): ?>
      <div class="bg-white rounded-2xl border border-gray-100 p-12 text-center">
        <i data-lucide="zap-off" class="w-12 h-12 text-gray-300 mx-auto mb-4"></i>
        <h3 class="text-lg font-semibold text-gray-700 mb-2">LearnPulse AI is not available</h3>
        <p class="text-gray-400 text-sm">The AI tutor feature has not been enabled by the administrator yet.</p>
      </div>
      <?php else: ?>
      <div class="bg-white rounded-2xl border border-gray-100 flex-1 flex flex-col overflow-hidden" style="max-height:65vh;">
        <div class="flex-1 overflow-y-auto p-5 space-y-4" id="chat-messages">
          <!-- Welcome message -->
          <div class="flex gap-3">
            <div class="w-9 h-9 bg-gradient-to-br from-indigo-600 to-cyan-500 rounded-xl flex items-center justify-center flex-shrink-0">
              <i data-lucide="zap" class="w-4 h-4 text-white"></i>
            </div>
            <div class="bg-indigo-50 rounded-2xl rounded-tl-sm px-4 py-3 max-w-md">
              <p class="text-sm font-semibold text-indigo-700 mb-1">LearnPulse AI</p>
              <p class="text-sm text-gray-700">Hello <?= htmlspecialchars(explode(' ',$_SESSION['full_name'])[0]) ?>! 👋 I'm your AI tutor. Ask me anything about your courses, tech concepts, or learning resources!</p>
            </div>
          </div>
        </div>

        <div class="border-t border-gray-100 p-4">
          <div class="flex gap-3">
            <input type="text" id="ai-input"
              placeholder="Ask LearnPulse anything..."
              class="flex-1 px-4 py-3 border border-gray-200 rounded-xl text-sm focus:outline-none focus:ring-2 focus:ring-indigo-500" />
            <button id="ai-send" onclick="sendMessage()"
              class="bg-indigo-600 text-white px-5 py-3 rounded-xl text-sm font-medium hover:bg-indigo-700 flex items-center gap-2 disabled:opacity-50">
              <i data-lucide="send" class="w-4 h-4"></i> Ask
            </button>
          </div>
          <p class="text-xs text-gray-400 mt-2 text-center">Powered by AI · Responses may not always be accurate · Always verify important information</p>
        </div>
      </div>
      <?php endif; ?>
    </main>
    <?php require_once $_SERVER['DOCUMENT_ROOT'] . '/ByteClass/includes/footer-minimal.php'; ?>
  </div>
</div>

<?php if ($ai_enabled && $ai_enabled !== '0'): ?>
<script>
const chatBox = document.getElementById('chat-messages');

document.getElementById('ai-input').addEventListener('keydown', function(e) {
  if (e.key === 'Enter' && !e.shiftKey) { e.preventDefault(); sendMessage(); }
});

async function sendMessage() {
  const input = document.getElementById('ai-input');
  const btn   = document.getElementById('ai-send');
  const msg   = input.value.trim();
  if (!msg) return;

  input.value = '';
  btn.disabled = true;

  // Add user message
  chatBox.innerHTML += `
    <div class="flex gap-3 flex-row-reverse">
      <div class="w-9 h-9 bg-indigo-600 rounded-xl flex items-center justify-center flex-shrink-0 text-white text-sm font-bold">
        <?= strtoupper(substr($_SESSION['full_name'],0,1)) ?>
      </div>
      <div class="bg-indigo-600 text-white rounded-2xl rounded-tr-sm px-4 py-3 max-w-md">
        <p class="text-sm">${escHtml(msg)}</p>
      </div>
    </div>`;
  chatBox.scrollTop = chatBox.scrollHeight;

  // Typing indicator
  const typingId = 'typing-' + Date.now();
  chatBox.innerHTML += `
    <div class="flex gap-3" id="${typingId}">
      <div class="w-9 h-9 bg-gradient-to-br from-indigo-600 to-cyan-500 rounded-xl flex items-center justify-center flex-shrink-0">
        <i data-lucide="zap" class="w-4 h-4 text-white"></i>
      </div>
      <div class="bg-indigo-50 rounded-2xl rounded-tl-sm px-4 py-3">
        <div class="flex gap-1 items-center h-5">
          <div class="w-2 h-2 bg-indigo-400 rounded-full animate-bounce" style="animation-delay:0ms"></div>
          <div class="w-2 h-2 bg-indigo-400 rounded-full animate-bounce" style="animation-delay:150ms"></div>
          <div class="w-2 h-2 bg-indigo-400 rounded-full animate-bounce" style="animation-delay:300ms"></div>
        </div>
      </div>
    </div>`;
  chatBox.scrollTop = chatBox.scrollHeight;
  lucide.createIcons();

  try {
    const res = await fetch('<?= APP_URL ?>/api/ai/chat.php', {
      method: 'POST',
      headers: { 'Content-Type': 'application/json' },
      body: JSON.stringify({ message: msg })
    });
    const data = await res.json();
    document.getElementById(typingId)?.remove();

    const reply = data.success ? data.data.reply : 'Sorry, I could not process that. Please try again.';
    chatBox.innerHTML += `
      <div class="flex gap-3">
        <div class="w-9 h-9 bg-gradient-to-br from-indigo-600 to-cyan-500 rounded-xl flex items-center justify-center flex-shrink-0">
          <i data-lucide="zap" class="w-4 h-4 text-white"></i>
        </div>
        <div class="bg-indigo-50 rounded-2xl rounded-tl-sm px-4 py-3 max-w-lg">
          <p class="text-sm font-semibold text-indigo-700 mb-1">LearnPulse AI</p>
          <p class="text-sm text-gray-700 whitespace-pre-wrap">${escHtml(reply)}</p>
        </div>
      </div>`;
  } catch(e) {
    document.getElementById(typingId)?.remove();
    chatBox.innerHTML += `<div class="text-center text-sm text-red-500">Connection error. Please try again.</div>`;
  }

  chatBox.scrollTop = chatBox.scrollHeight;
  btn.disabled = false;
  lucide.createIcons();
}

function escHtml(str) {
  return String(str).replace(/&/g,'&amp;').replace(/</g,'&lt;').replace(/>/g,'&gt;');
}
</script>
<?php endif; ?>
<?php require_once $_SERVER['DOCUMENT_ROOT'] . '/ByteClass/includes/scripts.php'; ?>
