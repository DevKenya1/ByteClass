<script>
const APP_URL = '<?= APP_URL ?>';
const USER_ROLE = '<?= $_SESSION["role"] ?? "" ?>';
const USER_ID = '<?= $_SESSION["user_id"] ?? "" ?>';
const TOKEN = '<?= $_SESSION["token"] ?? "" ?>';

// Sidebar toggle
let sidebarOpen = localStorage.getItem('sidebar_open') !== 'false';

function toggleSidebar() {
  sidebarOpen = !sidebarOpen;
  localStorage.setItem('sidebar_open', sidebarOpen);
  applySidebar();
}

function applySidebar() {
  const sidebar  = document.getElementById('sidebar');
  const content  = document.getElementById('main-content');
  
  const labels   = document.querySelectorAll('.sidebar-label');

  if (sidebarOpen) {
    sidebar.style.width = '256px';
    if (content) content.style.marginLeft = '256px';
    
    labels.forEach(l => l.classList.remove('hidden'));
    // Always keep ByteClass text visible
  } else {
    sidebar.style.width = '64px';
    if (content) content.style.marginLeft = '64px';
    
    labels.forEach(l => {
      // Don't hide the brand name in the logo
      if (!l.closest('a[href]')?.querySelector('.text-cyan-400')) {
        l.classList.add('hidden');
      }
    });
  }
  lucide.createIcons();
}

// API helper
async function api(endpoint, options = {}) {
  try {
    const res = await fetch(APP_URL + '/api/' + endpoint, {
      headers: {
        'Content-Type': 'application/json',
        'Authorization': 'Bearer ' + TOKEN,
        ...options.headers
      },
      ...options
    });
    const data = await res.json();
    return data;
  } catch(e) {
    console.error('API error:', e);
    return { success: false, message: e.message, data: [] };
  }
}

// Toast notifications
function toast(message, type = 'success') {
  const colors = {
    success: 'bg-green-600',
    error:   'bg-red-600',
    warning: 'bg-amber-500',
    info:    'bg-indigo-600',
  };
  const t = document.createElement('div');
  t.className = `fixed top-4 right-4 z-50 ${colors[type]} text-white px-5 py-3 rounded-xl shadow-lg text-sm font-medium flex items-center gap-2 animate-pulse`;
  t.innerHTML = message;
  document.body.appendChild(t);
  setTimeout(() => t.remove(), 4000);
}

// Dropdown toggle
function toggleDropdown(id) {
  const el = document.getElementById(id);
  el.classList.toggle('hidden');
}

// Click outside to close dropdowns
document.addEventListener('click', function(e) {
  document.querySelectorAll('[data-dropdown]').forEach(d => {
    if (!d.contains(e.target)) {
      const menu = document.getElementById(d.dataset.dropdown);
      if (menu) menu.classList.add('hidden');
    }
  });
});

// Mobile menu
const mobileBtn = document.getElementById('mobile-menu-btn');
const mobileMenu = document.getElementById('mobile-menu');
if (mobileBtn && mobileMenu) {
  mobileBtn.addEventListener('click', () => mobileMenu.classList.toggle('hidden'));
}

// Init
document.addEventListener('DOMContentLoaded', () => {
  applySidebar();
  lucide.createIcons();
  loadNotifications();
});

// Load notification count
async function loadNotifications() {
  try {
    const res = await api('notifications/index.php');
    if (res.success) {
      const unread = res.data.filter(n => !n.is_read).length;
      const badge  = document.getElementById('notif-badge');
      if (badge && unread > 0) {
        badge.textContent = unread > 9 ? '9+' : unread;
        badge.classList.remove('hidden');
      }
    }
  } catch(e) {}
}

// Auto logout countdown
let inactiveTimer;
function resetTimer() {
  clearTimeout(inactiveTimer);
  inactiveTimer = setTimeout(() => {
    window.location.href = APP_URL + '/api/auth/logout.php?redirect=1';
  }, <?= AUTO_LOGOUT_MINUTES * 60 * 1000 ?>);
}
['mousemove','keydown','click','scroll','touchstart'].forEach(e => {
  document.addEventListener(e, resetTimer, true);
});
resetTimer();
</script>





