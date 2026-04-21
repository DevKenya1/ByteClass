<?php
// Floating public help chat widget
// Stores messages in DB table: public_chat_sessions
// Admin responds via admin/support.php
$chat_token = $_COOKIE['bc_chat_token'] ?? '';
if (!$chat_token) {
    $chat_token = bin2hex(random_bytes(16));
    setcookie('bc_chat_token', $chat_token, time()+86400*30, '/');
}
?>
<!-- Floating chat button -->
<div id="float-chat-btn"
  onclick="toggleFloatChat()"
  class="fixed bottom-6 right-6 z-50 w-14 h-14 bg-indigo-600 hover:bg-indigo-700 rounded-full shadow-lg cursor-pointer flex items-center justify-center transition-all hover:scale-110"
  title="Ask us anything">
  <span id="float-chat-icon-q" class="text-white text-2xl font-black leading-none">?</span>
  <span id="float-chat-icon-x" class="hidden text-white text-xl font-bold">✕</span>
  <span id="float-chat-badge" class="hidden absolute -top-1 -right-1 w-5 h-5 bg-red-500 text-white text-xs rounded-full flex items-center justify-center font-bold">1</span>
</div>

<!-- Chat window -->
<div id="float-chat-window"
  class="hidden fixed bottom-24 right-6 z-50 w-80 bg-white rounded-2xl shadow-2xl border border-gray-200 overflow-hidden flex flex-col"
  style="max-height:440px;">
  <div class="bg-gradient-to-r from-indigo-600 to-cyan-500 px-4 py-3 flex items-center gap-3">
    <div class="w-8 h-8 bg-white bg-opacity-20 rounded-full flex items-center justify-center flex-shrink-0">
      <span class="text-white text-sm">?</span>
    </div>
    <div class="flex-1">
      <p class="text-white text-sm font-semibold">ByteClass Support</p>
      <p class="text-indigo-200 text-xs">Ask us anything · Usually replies quickly</p>
    </div>
  </div>

  <div id="float-chat-messages"
    class="flex-1 overflow-y-auto px-4 py-3 space-y-3"
    style="min-height:200px;max-height:240px;">
    <div class="flex gap-2">
      <div class="w-6 h-6 bg-indigo-600 rounded-full flex items-center justify-center text-white text-xs font-bold flex-shrink-0 mt-1">B</div>
      <div class="bg-gray-100 rounded-2xl rounded-tl-sm px-3 py-2 max-w-xs">
        <p class="text-sm text-gray-800">Hi! 👋 How can we help you today? Ask us anything about ByteClass.</p>
      </div>
    </div>
  </div>

  <form id="float-chat-form" onsubmit="sendFloatMsg(event)"
    class="border-t border-gray-100 p-3 flex gap-2">
    <input type="text" id="float-chat-input"
      placeholder="Type your question..."
      maxlength="500" autocomplete="off"
      class="flex-1 px-3 py-2 border border-gray-200 rounded-xl text-sm focus:outline-none focus:ring-2 focus:ring-indigo-500" />
    <button type="submit"
      class="bg-indigo-600 text-white w-9 h-9 rounded-xl flex items-center justify-center hover:bg-indigo-700 flex-shrink-0">
      <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
        <line x1="22" y1="2" x2="11" y2="13"/><polygon points="22,2 15,22 11,13 2,9"/>
      </svg>
    </button>
  </form>
</div>

<script>
const CHAT_TOKEN = '<?= htmlspecialchars($chat_token) ?>';
let chatOpen = false;

function toggleFloatChat() {
  chatOpen = !chatOpen;
  const win  = document.getElementById('float-chat-window');
  const iconQ = document.getElementById('float-chat-icon-q');
  const iconX = document.getElementById('float-chat-icon-x');
  if (chatOpen) {
    win.classList.remove('hidden');
    iconQ.classList.add('hidden');
    iconX.classList.remove('hidden');
    document.getElementById('float-chat-input').focus();
    loadMessages();
  } else {
    win.classList.add('hidden');
    iconQ.classList.remove('hidden');
    iconX.classList.add('hidden');
    // Delete all messages on close
    clearMessages();
  }
}

async function loadMessages() {
  try {
    const res  = await fetch('<?= APP_URL ?>/api/chat/public.php?action=load&token=' + CHAT_TOKEN);
    const data = await res.json();
    if (data.success && data.data.messages.length > 1) {
      const box = document.getElementById('float-chat-messages');
      box.innerHTML = '';
      data.data.messages.forEach(m => appendMsg(m.message, m.is_admin, m.sender_name));
    }
  } catch(e) {}
}

async function clearMessages() {
  try {
    await fetch('<?= APP_URL ?>/api/chat/public.php?action=clear&token=' + CHAT_TOKEN, {method:'POST'});
  } catch(e) {}
}

async function sendFloatMsg(e) {
  e.preventDefault();
  const input = document.getElementById('float-chat-input');
  const msg   = input.value.trim();
  if (!msg) return;
  input.value = '';
  appendMsg(msg, false, 'You');

  try {
    const res  = await fetch('<?= APP_URL ?>/api/chat/public.php?action=send&token=' + CHAT_TOKEN, {
      method: 'POST',
      headers: {'Content-Type':'application/json'},
      body: JSON.stringify({message: msg})
    });
    const data = await res.json();
    if (data.success) {
      // Auto-reply after short delay
      setTimeout(() => {
        appendMsg("Thanks for your message! Our team will respond soon. For urgent help, email us or check our FAQ.", true, 'ByteClass Support');
      }, 800);
    }
  } catch(e) {
    appendMsg('Could not send message. Please try again.', true, 'System');
  }
}

function appendMsg(text, isAdmin, senderName) {
  const box = document.getElementById('float-chat-messages');
  const wrap = document.createElement('div');
  wrap.className = 'flex gap-2 ' + (isAdmin ? '' : 'flex-row-reverse');
  wrap.innerHTML = `
    <div class="w-6 h-6 ${isAdmin ? 'bg-indigo-600' : 'bg-gray-500'} rounded-full flex items-center justify-center text-white text-xs font-bold flex-shrink-0 mt-1">
      ${senderName.charAt(0).toUpperCase()}
    </div>
    <div class="${isAdmin ? 'bg-gray-100 text-gray-800 rounded-tl-sm' : 'bg-indigo-600 text-white rounded-tr-sm'} rounded-2xl px-3 py-2 max-w-48 text-sm">
      ${escHtml(text)}
    </div>`;
  box.appendChild(wrap);
  box.scrollTop = box.scrollHeight;
}

function escHtml(s) {
  return String(s).replace(/&/g,'&amp;').replace(/</g,'&lt;').replace(/>/g,'&gt;');
}
</script>
