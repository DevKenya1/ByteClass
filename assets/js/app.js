// ByteClass Global JS
document.addEventListener('DOMContentLoaded', function() {
  lucide.createIcons();

  // Mobile menu
  const btn  = document.getElementById('mobile-menu-btn');
  const menu = document.getElementById('mobile-menu');
  if (btn && menu) {
    btn.addEventListener('click', () => menu.classList.toggle('hidden'));
  }
});
