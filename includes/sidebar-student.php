<?php
$current_path = $_SERVER['REQUEST_URI'];
$photo    = $_SESSION['photo'] ?? null;
$initials = strtoupper(substr($_SESSION['full_name'] ?? 'S', 0, 1));
?>
<aside id="sidebar" class="fixed left-0 top-0 h-full transition-all duration-300 z-40 flex flex-col"
  style="width:256px;background:linear-gradient(180deg,#312e81 0%,#1e1b4b 40%,#0f172a 100%);">

  <div style="padding:14px 20px;border-bottom:1px solid rgba(255,255,255,0.1);display:flex;align-items:center;flex-shrink:0;">
    <a href="<?= APP_URL ?>/" style="text-decoration:none;white-space:nowrap;">
      <span style="font-size:21px;font-weight:800;letter-spacing:-0.5px;line-height:1;">
        <span style="color:#22d3ee;">Byte</span><span style="color:#fff;">Class</span>
      </span>
    </a>
  </div>

  <nav class="flex-1 py-3 overflow-y-auto px-2">
    <?php
    $links = [
      ['/student/dashboard.php',    'layout-dashboard', 'Dashboard'],
      ['/student/courses.php',      'book-open',        'My courses'],
      ['/student/explore.php',      'compass',          'Explore courses'],
      ['/student/sessions.php',     'calendar',         'Class sessions'],
      ['/student/submissions.php',  'clipboard-list',   'Submissions'],
      ['/student/community.php',    'message-square',   'Community'],
      ['/student/learnpulse.php',   'zap',              'LearnPulse AI'],
      ['/student/certificates.php', 'award',            'Certificates'],
      ['/student/support.php',      'headphones',       'Support'],
      ['/student/leaderboard.php',  'trophy',           'Leaderboard'],
    ];
    foreach ($links as [$url, $icon, $label]):
      $active = str_contains($current_path, $url);
    ?>
    <a href="<?= APP_URL . $url ?>" title="<?= $label ?>"
       class="flex items-center gap-3 px-3 py-2.5 rounded-xl mb-0.5 transition-all relative
       <?= $active ? 'bg-white bg-opacity-20 text-white' : 'text-gray-400 hover:bg-white hover:bg-opacity-10 hover:text-white' ?>">
      <i data-lucide="<?= $icon ?>" class="w-5 h-5 flex-shrink-0 <?= $active ? 'text-cyan-400' : '' ?>"></i>
      <span class="text-sm font-medium sidebar-label whitespace-nowrap"><?= $label ?></span>
      <?php if ($active): ?>
      <div class="absolute left-0 top-1/2 -translate-y-1/2 w-1 h-6 bg-cyan-400 rounded-r-full"></div>
      <?php endif; ?>
    </a>
    <?php endforeach; ?>
  </nav>

  <div style="border-top:1px solid rgba(255,255,255,0.1);padding:14px 16px;flex-shrink:0;">
    <div class="flex items-center gap-3">
      <?php if ($photo): ?>
      <img src="<?= htmlspecialchars($photo) ?>" alt="Photo"
           style="width:36px;height:36px;border-radius:50%;object-fit:cover;flex-shrink:0;border:2px solid rgba(255,255,255,0.3);" />
      <?php else: ?>
      <div style="width:36px;height:36px;border-radius:50%;background:linear-gradient(135deg,#4F46E5,#06B6D4);display:flex;align-items:center;justify-content:center;color:#fff;font-weight:700;font-size:14px;flex-shrink:0;">
        <?= $initials ?>
      </div>
      <?php endif; ?>
      <div class="sidebar-label overflow-hidden">
        <p style="color:#fff;font-size:12px;font-weight:600;white-space:nowrap;overflow:hidden;text-overflow:ellipsis;line-height:1.3;"><?= htmlspecialchars($_SESSION['full_name'] ?? '') ?></p>
        <p style="color:#9ca3af;font-size:11px;line-height:1.3;">Student</p>
      </div>
    </div>
  </div>
</aside>
