<?php
$yr = date('Y');
$cfg = [];
try {
    if (isset($db)) {
        $s = $db->query("SELECT setting_key, setting_value FROM system_settings WHERE setting_key LIKE 'social_%'");
        foreach ($s->fetchAll() as $r) $cfg[$r['setting_key']] = $r['setting_value'];
    }
} catch (Exception $e) {}
?>
<footer class="bg-gray-900 text-gray-400">
  <div class="max-w-7xl mx-auto px-6 py-14 grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-10">

    <!-- Brand -->
    <div class="lg:col-span-1">
      <div class="mb-5">
        <a href="<?= APP_URL ?>/pages/public/index.php" class="inline-block">
          <span class="text-2xl font-black"><span class="text-cyan-400">Byte</span><span class="text-white">Class</span></span>
        </a>
        <p class="text-gray-500 text-xs tracking-widest uppercase font-medium mt-1">Learn · Build · Grow</p>
      </div>
      <p class="text-sm leading-relaxed mb-5">Kenya's premier tech education platform. Learn IT support, cybersecurity, networking and more — fully online.</p>
      <div class="flex items-center gap-3">
        <?php if (!empty($cfg['social_website'])): ?>
        <a href="<?= htmlspecialchars($cfg['social_website']) ?>" target="_blank" class="w-8 h-8 bg-gray-800 hover:bg-indigo-600 rounded-lg flex items-center justify-center transition-colors"><i data-lucide="globe" class="w-4 h-4 text-gray-300"></i></a>
        <?php endif; ?>
        <?php if (!empty($cfg['social_twitter'])): ?>
        <a href="<?= htmlspecialchars($cfg['social_twitter']) ?>" target="_blank" class="w-8 h-8 bg-gray-800 hover:bg-gray-600 rounded-lg flex items-center justify-center transition-colors"><i data-lucide="twitter" class="w-4 h-4 text-gray-300"></i></a>
        <?php endif; ?>
        <?php if (!empty($cfg['social_whatsapp'])): ?>
        <a href="<?= htmlspecialchars($cfg['social_whatsapp']) ?>" target="_blank" class="w-8 h-8 bg-gray-800 hover:bg-green-600 rounded-lg flex items-center justify-center transition-colors"><i data-lucide="message-circle" class="w-4 h-4 text-gray-300"></i></a>
        <?php endif; ?>
        <?php if (!empty($cfg['social_instagram'])): ?>
        <a href="<?= htmlspecialchars($cfg['social_instagram']) ?>" target="_blank" class="w-8 h-8 bg-gray-800 hover:bg-pink-600 rounded-lg flex items-center justify-center transition-colors"><i data-lucide="instagram" class="w-4 h-4 text-gray-300"></i></a>
        <?php endif; ?>
        <?php if (!empty($cfg['social_facebook'])): ?>
        <a href="<?= htmlspecialchars($cfg['social_facebook']) ?>" target="_blank" class="w-8 h-8 bg-gray-800 hover:bg-blue-600 rounded-lg flex items-center justify-center transition-colors"><i data-lucide="facebook" class="w-4 h-4 text-gray-300"></i></a>
        <?php endif; ?>
      </div>
    </div>

    <!-- Learn -->
    <div>
      <h3 class="text-white font-bold mb-4 text-sm uppercase tracking-wide">Learn</h3>
      <ul class="space-y-2.5 text-sm">
        <li><a href="<?= APP_URL ?>/pages/public/courses.php" class="hover:text-white hover:translate-x-1 inline-block transition-all">Browse Courses</a></li>
        <li><a href="<?= APP_URL ?>/pages/auth/register.php" class="hover:text-white hover:translate-x-1 inline-block transition-all">Create Free Account</a></li>
        <li><a href="<?= APP_URL ?>/pages/auth/login.php" class="hover:text-white hover:translate-x-1 inline-block transition-all">Student Login</a></li>
        <li><a href="<?= APP_URL ?>/pages/public/faq.php" class="hover:text-white hover:translate-x-1 inline-block transition-all">FAQ</a></li>
        <li><a href="<?= APP_URL ?>/pages/public/about.php#courses" class="hover:text-white hover:translate-x-1 inline-block transition-all">Learning Paths</a></li>
      </ul>
    </div>

    <!-- Company -->
    <div>
      <h3 class="text-white font-bold mb-4 text-sm uppercase tracking-wide">Company</h3>
      <ul class="space-y-2.5 text-sm">
        <li><a href="<?= APP_URL ?>/pages/public/about.php" class="hover:text-white hover:translate-x-1 inline-block transition-all">About ByteClass</a></li>
        <li><a href="<?= APP_URL ?>/pages/public/contact.php" class="hover:text-white hover:translate-x-1 inline-block transition-all">Contact Us</a></li>
        <li><a href="<?= APP_URL ?>/pages/public/contact.php?subject=Lecturer+Application" class="hover:text-white hover:translate-x-1 inline-block transition-all">Become a Lecturer</a></li>
        <li><a href="<?= APP_URL ?>/pages/public/contact.php?subject=Partnership+Opportunity" class="hover:text-white hover:translate-x-1 inline-block transition-all">Partnerships</a></li>
      </ul>
    </div>

    <!-- Support & Legal -->
    <div>
      <h3 class="text-white font-bold mb-4 text-sm uppercase tracking-wide">Support & Legal</h3>
      <ul class="space-y-2.5 text-sm">
        <li><a href="<?= APP_URL ?>/pages/public/faq.php" class="hover:text-white hover:translate-x-1 inline-block transition-all">Help Center & FAQ</a></li>
        <li><a href="<?= APP_URL ?>/pages/public/contact.php" class="hover:text-white hover:translate-x-1 inline-block transition-all">Support</a></li>
        <li><a href="<?= APP_URL ?>/pages/public/terms.php" class="hover:text-white hover:translate-x-1 inline-block transition-all">Terms & Conditions</a></li>
        <li><a href="<?= APP_URL ?>/pages/public/privacy.php" class="hover:text-white hover:translate-x-1 inline-block transition-all">Privacy Policy</a></li>
        <li><a href="mailto:legal@byteclass.io" class="hover:text-white hover:translate-x-1 inline-block transition-all">Legal</a></li>
      </ul>
    </div>
  </div>

  <div class="border-t border-gray-800">
    <div class="max-w-7xl mx-auto px-6 py-5 flex flex-col sm:flex-row items-center justify-between gap-3">
      <p class="text-xs text-gray-500">&copy; <?= $yr ?> ByteClass Ltd. All rights reserved. Nairobi, Kenya.</p>
      <div class="flex items-center gap-4 text-xs">
        <a href="<?= APP_URL ?>/pages/public/privacy.php" class="text-gray-500 hover:text-white transition-colors">Privacy</a>
        <span class="text-gray-700">·</span>
        <a href="<?= APP_URL ?>/pages/public/terms.php" class="text-gray-500 hover:text-white transition-colors">Terms</a>
        <span class="text-gray-700">·</span>
        <a href="<?= APP_URL ?>/pages/public/contact.php" class="text-gray-500 hover:text-white transition-colors">Contact</a>
      </div>
    </div>
  </div>
</footer>
<?php require_once $_SERVER['DOCUMENT_ROOT'] . '/ByteClass/includes/floating-chat.php'; ?>
<script>
document.addEventListener('DOMContentLoaded', () => {
  if (typeof lucide !== 'undefined') lucide.createIcons();
});
</script>
</body>
</html>

