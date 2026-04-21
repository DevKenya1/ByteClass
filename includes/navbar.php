<?php
$current = $_SERVER['REQUEST_URI'];
$is_logged = !empty($_SESSION['user_id']);
$dash_link = '';
if ($is_logged) {
    $dash_link = match($_SESSION['role'] ?? '') {
        'admin'    => APP_URL . '/admin/dashboard.php',
        'lecturer' => APP_URL . '/lecturer/dashboard.php',
        default    => APP_URL . '/student/dashboard.php',
    };
}
$nav_links = [
    ['href' => APP_URL . '/pages/public/courses.php', 'label' => 'Courses'],
    ['href' => APP_URL . '/pages/public/about.php',   'label' => 'About'],
    ['href' => APP_URL . '/pages/public/faq.php',     'label' => 'FAQ'],
    ['href' => APP_URL . '/pages/public/contact.php', 'label' => 'Contact'],
];
?>
<nav id="main-nav" class="bg-white/95 backdrop-blur-sm border-b border-gray-100 sticky top-0 z-50 transition-all duration-300">
  <div class="max-w-7xl mx-auto px-6">
    <div class="flex items-center justify-between h-16">

      <!-- Logo + Tagline -->
      <a href="<?= APP_URL ?>/pages/public/index.php" class="flex flex-col leading-none group">
        <span class="text-xl font-black tracking-tight">
          <span class="text-cyan-500 group-hover:text-cyan-600 transition-colors">Byte</span><span class="text-gray-900">Class</span>
        </span>
        <span class="text-gray-400 text-[10px] font-medium tracking-widest uppercase mt-0.5">Learn · Build · Grow</span>
      </a>

      <!-- Desktop nav links -->
      <div class="hidden md:flex items-center gap-1">
        <?php foreach ($nav_links as $link):
          $active = str_contains($current, basename($link['href']));
        ?>
        <a href="<?= $link['href'] ?>"
           class="px-4 py-2 rounded-xl text-sm font-medium transition-all <?= $active ? 'text-indigo-600 bg-indigo-50' : 'text-gray-600 hover:text-indigo-600 hover:bg-gray-50' ?>">
          <?= $link['label'] ?>
        </a>
        <?php endforeach; ?>
      </div>

      <!-- CTA buttons -->
      <div class="hidden md:flex items-center gap-3">
        <?php if ($is_logged): ?>
        <a href="<?= $dash_link ?>"
           class="flex items-center gap-2 bg-indigo-600 text-white px-5 py-2.5 rounded-xl text-sm font-semibold hover:bg-indigo-700 transition-all shadow-sm hover:shadow-md">
          <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5"><rect x="3" y="3" width="7" height="7"/><rect x="14" y="3" width="7" height="7"/><rect x="3" y="14" width="7" height="7"/><rect x="14" y="14" width="7" height="7"/></svg>
          Dashboard
        </a>
        <?php else: ?>
        <a href="<?= APP_URL ?>/pages/auth/login.php"
           class="px-5 py-2.5 text-sm font-semibold text-gray-700 hover:text-indigo-600 rounded-xl hover:bg-gray-50 transition-all">
          Sign in
        </a>
        <a href="<?= APP_URL ?>/pages/auth/register.php"
           class="flex items-center gap-2 bg-indigo-600 text-white px-5 py-2.5 rounded-xl text-sm font-semibold hover:bg-indigo-700 transition-all shadow-sm hover:shadow-md">
          <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5"><path d="M20 21v-2a4 4 0 0 0-4-4H8a4 4 0 0 0-4 4v2"/><circle cx="12" cy="7" r="4"/></svg>
          Get Started Free
        </a>
        <?php endif; ?>
      </div>

      <!-- Mobile menu button -->
      <button id="mobile-menu-btn" onclick="toggleMobileMenu()"
        class="md:hidden p-2 text-gray-600 hover:text-gray-900 rounded-xl hover:bg-gray-100 transition-colors">
        <svg id="menu-icon" width="22" height="22" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
          <line x1="3" y1="6" x2="21" y2="6"/><line x1="3" y1="12" x2="21" y2="12"/><line x1="3" y1="18" x2="21" y2="18"/>
        </svg>
        <svg id="close-icon" width="22" height="22" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" class="hidden">
          <line x1="18" y1="6" x2="6" y2="18"/><line x1="6" y1="6" x2="18" y2="18"/>
        </svg>
      </button>
    </div>

    <!-- Mobile menu -->
    <div id="mobile-menu" class="hidden md:hidden border-t border-gray-100 py-3 pb-4 space-y-1">
      <?php foreach ($nav_links as $link): ?>
      <a href="<?= $link['href'] ?>"
         class="block px-4 py-2.5 rounded-xl text-sm font-medium text-gray-700 hover:text-indigo-600 hover:bg-indigo-50 transition-colors">
        <?= $link['label'] ?>
      </a>
      <?php endforeach; ?>
      <div class="pt-3 mt-2 border-t border-gray-100 flex flex-col gap-2">
        <?php if ($is_logged): ?>
        <a href="<?= $dash_link ?>" class="text-center bg-indigo-600 text-white px-4 py-3 rounded-xl text-sm font-bold hover:bg-indigo-700 transition-colors">Dashboard</a>
        <?php else: ?>
        <a href="<?= APP_URL ?>/pages/auth/login.php" class="text-center px-4 py-2.5 border border-gray-200 rounded-xl text-sm font-semibold text-gray-700 hover:bg-gray-50 transition-colors">Sign in</a>
        <a href="<?= APP_URL ?>/pages/auth/register.php" class="text-center bg-indigo-600 text-white px-4 py-3 rounded-xl text-sm font-bold hover:bg-indigo-700 transition-colors">Get Started Free</a>
        <?php endif; ?>
      </div>
    </div>
  </div>
</nav>
<script>
function toggleMobileMenu() {
  const menu = document.getElementById('mobile-menu');
  const menuIcon = document.getElementById('menu-icon');
  const closeIcon = document.getElementById('close-icon');
  menu.classList.toggle('hidden');
  menuIcon.classList.toggle('hidden');
  closeIcon.classList.toggle('hidden');
}
// Shrink navbar on scroll
window.addEventListener('scroll', () => {
  const nav = document.getElementById('main-nav');
  if (window.scrollY > 20) {
    nav.classList.add('shadow-md');
  } else {
    nav.classList.remove('shadow-md');
  }
});
</script>
