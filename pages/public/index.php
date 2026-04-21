<?php
require_once $_SERVER['DOCUMENT_ROOT'] . '/ByteClass/config/constants.php';
require_once $_SERVER['DOCUMENT_ROOT'] . '/ByteClass/config/session.php';
require_once $_SERVER['DOCUMENT_ROOT'] . '/ByteClass/config/database.php';

if (!empty($_SESSION['user_id']) && !empty($_SESSION['role'])) {
    $role = $_SESSION['role'];
    if ($role==='admin')        { header('Location:'.APP_URL.'/admin/dashboard.php'); exit; }
    elseif ($role==='lecturer') { header('Location:'.APP_URL.'/lecturer/dashboard.php'); exit; }
    else                        { header('Location:'.APP_URL.'/student/dashboard.php'); exit; }
}

$db = Database::getInstance()->getConnection();
$courses = $db->query("
    SELECT c.id, c.name, c.thumbnail, c.category, c.difficulty, c.price_kes, c.price_usd,
        (SELECT COUNT(*) FROM enrollments e WHERE e.course_id=c.id) AS enrolled_count,
        u.full_name AS lecturer_name
    FROM courses c
    LEFT JOIN course_lecturers cl ON cl.course_id=c.id
    LEFT JOIN users u ON u.id=cl.lecturer_id
    WHERE c.status='published'
    GROUP BY c.id ORDER BY c.created_at DESC LIMIT 6
")->fetchAll();

$total_students  = (int)$db->query("SELECT COUNT(*) FROM users WHERE role='student' AND status='active'")->fetchColumn();
$total_courses   = (int)$db->query("SELECT COUNT(*) FROM courses WHERE status='published'")->fetchColumn();
$total_lecturers = (int)$db->query("SELECT COUNT(*) FROM users WHERE role='lecturer' AND status='active'")->fetchColumn();

$page_title = 'Learn · Build · Grow — ByteClass';
require_once $_SERVER['DOCUMENT_ROOT'] . '/ByteClass/includes/head.php';
require_once $_SERVER['DOCUMENT_ROOT'] . '/ByteClass/includes/navbar.php';
?>

<!-- ========== HERO ========== -->
<section class="relative min-h-screen flex items-center overflow-hidden bg-gray-900">
  <!-- Background image -->
  <div class="absolute inset-0">
    <img src="https://images.unsplash.com/photo-1517077304055-6e89abbf09b0?w=1600&q=80&auto=format&fit=crop"
      alt="Tech learning" class="w-full h-full object-cover opacity-30" loading="eager" />
    <div class="absolute inset-0 bg-gradient-to-br from-indigo-900/90 via-indigo-800/80 to-cyan-900/70"></div>
  </div>

  <!-- Floating blobs -->
  <div class="absolute top-20 left-10 w-64 h-64 bg-cyan-400 rounded-full blur-3xl opacity-10 animate-pulse"></div>
  <div class="absolute bottom-20 right-10 w-96 h-96 bg-indigo-400 rounded-full blur-3xl opacity-10 animate-pulse" style="animation-delay:1s"></div>

  <div class="relative max-w-7xl mx-auto px-6 py-24 grid grid-cols-1 lg:grid-cols-2 gap-16 items-center">
    <div>
      <div class="inline-flex items-center gap-2 bg-white/10 border border-white/20 backdrop-blur-sm rounded-full px-4 py-2 mb-6">
        <span class="w-2 h-2 bg-green-400 rounded-full animate-pulse"></span>
        <span class="text-white text-sm font-medium"><?= $total_courses ?> courses · <?= $total_students ?>+ students enrolled</span>
      </div>
      <h1 class="text-5xl md:text-6xl font-black text-white leading-[1.1] mb-6">
        The Future of<br>
        <span class="text-transparent bg-clip-text bg-gradient-to-r from-cyan-400 to-indigo-300">Tech Education</span><br>
        in Africa
      </h1>
      <p class="text-xl text-indigo-200 leading-relaxed mb-8 max-w-lg">
        ByteClass connects ambitious learners with expert instructors. Master IT support, cybersecurity, networking and more — fully online, at your pace.
      </p>
      <div class="flex flex-wrap gap-4 mb-10">
        <a href="<?= APP_URL ?>/pages/auth/register.php"
           class="bg-white text-indigo-900 px-8 py-4 rounded-2xl font-bold text-lg hover:bg-indigo-50 transition-all shadow-xl hover:scale-105 flex items-center gap-3">
          <i data-lucide="zap" class="w-5 h-5"></i>
          Start for Free
        </a>
        <a href="#courses"
           class="border-2 border-white/30 text-white px-8 py-4 rounded-2xl font-bold text-lg hover:bg-white/10 transition-all backdrop-blur-sm flex items-center gap-3">
          <i data-lucide="play-circle" class="w-5 h-5"></i>
          Explore Courses
        </a>
      </div>
      <!-- Trust badges -->
      <div class="flex flex-wrap items-center gap-6">
        <?php foreach ([
          ['icon'=>'shield-check', 'text'=>'Verified Instructors'],
          ['icon'=>'award',        'text'=>'Industry Certificates'],
          ['icon'=>'zap',          'text'=>'AI-Powered Tutor'],
        ] as $b): ?>
        <div class="flex items-center gap-2">
          <i data-lucide="<?= $b['icon'] ?>" class="w-4 h-4 text-cyan-400"></i>
          <span class="text-indigo-200 text-sm"><?= $b['text'] ?></span>
        </div>
        <?php endforeach; ?>
      </div>
    </div>

    <!-- Hero image card -->
    <div class="hidden lg:block">
      <div class="relative">
        <div class="bg-white/10 backdrop-blur-sm border border-white/20 rounded-3xl p-6 shadow-2xl">
          <img src="https://images.unsplash.com/photo-1516321318423-f06f85e504b3?w=600&q=80&auto=format&fit=crop"
            alt="Students learning" class="w-full h-64 object-cover rounded-2xl mb-4" />
          <div class="space-y-3">
            <?php foreach ([
              ['label'=>'IT Support Fundamentals',       'progress'=>75, 'color'=>'bg-indigo-500'],
              ['label'=>'Ethical Hacking Basics',         'progress'=>40, 'color'=>'bg-cyan-500'],
              ['label'=>'Network Security Essentials',    'progress'=>90, 'color'=>'bg-green-500'],
            ] as $c): ?>
            <div class="flex items-center gap-3 bg-white/5 rounded-xl px-3 py-2">
              <div class="w-8 h-8 bg-white/10 rounded-lg flex items-center justify-center flex-shrink-0">
                <i data-lucide="book-open" class="w-4 h-4 text-white"></i>
              </div>
              <div class="flex-1">
                <p class="text-white text-xs font-medium"><?= $c['label'] ?></p>
                <div class="w-full bg-white/10 rounded-full h-1.5 mt-1">
                  <div class="<?= $c['color'] ?> h-1.5 rounded-full" style="width:<?= $c['progress'] ?>%"></div>
                </div>
              </div>
              <span class="text-white text-xs opacity-70"><?= $c['progress'] ?>%</span>
            </div>
            <?php endforeach; ?>
          </div>
        </div>
        <!-- Floating stat cards -->
        <div class="absolute -top-4 -left-4 bg-white rounded-2xl shadow-xl px-4 py-3 flex items-center gap-3">
          <div class="w-10 h-10 bg-amber-100 rounded-xl flex items-center justify-center">
            <i data-lucide="trophy" class="w-5 h-5 text-amber-600"></i>
          </div>
          <div>
            <p class="text-lg font-black text-gray-900"><?= $total_students ?>+</p>
            <p class="text-xs text-gray-500">Active students</p>
          </div>
        </div>
        <div class="absolute -bottom-4 -right-4 bg-white rounded-2xl shadow-xl px-4 py-3 flex items-center gap-3">
          <div class="w-10 h-10 bg-green-100 rounded-xl flex items-center justify-center">
            <i data-lucide="award" class="w-5 h-5 text-green-600"></i>
          </div>
          <div>
            <p class="text-lg font-black text-gray-900"><?= $total_courses ?>+</p>
            <p class="text-xs text-gray-500">Courses available</p>
          </div>
        </div>
      </div>
    </div>
  </div>

  <!-- Scroll indicator -->
  <div class="absolute bottom-8 left-1/2 -translate-x-1/2 animate-bounce">
    <i data-lucide="chevrons-down" class="w-6 h-6 text-white opacity-50"></i>
  </div>
</section>

<!-- ========== STATS ========== -->
<section class="bg-white py-16 border-b border-gray-100">
  <div class="max-w-6xl mx-auto px-6 grid grid-cols-2 lg:grid-cols-4 gap-8">
    <?php foreach ([
      ['value'=>$total_students.'+', 'label'=>'Students enrolled',  'icon'=>'users',          'color'=>'text-indigo-600'],
      ['value'=>$total_courses.'+',  'label'=>'Expert courses',      'icon'=>'book-open',       'color'=>'text-cyan-600'],
      ['value'=>$total_lecturers.'+','label'=>'Industry lecturers',  'icon'=>'graduation-cap', 'color'=>'text-green-600'],
      ['value'=>'24/7',              'label'=>'AI tutor availability','icon'=>'zap',             'color'=>'text-amber-600'],
    ] as $s): ?>
    <div class="text-center">
      <div class="w-14 h-14 bg-gray-50 rounded-2xl flex items-center justify-center mx-auto mb-3">
        <i data-lucide="<?= $s['icon'] ?>" class="w-7 h-7 <?= $s['color'] ?>"></i>
      </div>
      <p class="text-3xl font-black text-gray-900 mb-1"><?= $s['value'] ?></p>
      <p class="text-gray-500 text-sm"><?= $s['label'] ?></p>
    </div>
    <?php endforeach; ?>
  </div>
</section>

<!-- ========== HOW IT WORKS ========== -->
<section class="py-20 bg-gray-50">
  <div class="max-w-6xl mx-auto px-6">
    <div class="text-center mb-14">
      <span class="bg-indigo-100 text-indigo-700 text-xs font-bold px-3 py-1.5 rounded-full uppercase tracking-wide">How It Works</span>
      <h2 class="text-4xl font-black text-gray-900 mt-3 mb-3">Get started in 3 simple steps</h2>
      <p class="text-gray-500 max-w-xl mx-auto">From sign-up to certificate in weeks — not years.</p>
    </div>
    <div class="grid grid-cols-1 md:grid-cols-3 gap-8 relative">
      <!-- Connection line -->
      <div class="hidden md:block absolute top-12 left-1/3 right-1/3 h-0.5 bg-gradient-to-r from-indigo-200 to-cyan-200"></div>
      <?php foreach ([
        ['step'=>'01', 'icon'=>'user-plus',  'title'=>'Create Account',        'desc'=>'Sign up free in under 2 minutes. No credit card required to get started.',         'color'=>'bg-indigo-600'],
        ['step'=>'02', 'icon'=>'compass',    'title'=>'Choose Your Course',     'desc'=>'Browse our catalog of expert-taught tech courses. Enroll in free or paid courses.', 'color'=>'bg-cyan-500'],
        ['step'=>'03', 'icon'=>'award',      'title'=>'Learn & Get Certified',  'desc'=>'Complete lessons, take quizzes, and earn a verified certificate to boost your career.','color'=>'bg-green-500'],
      ] as $step): ?>
      <div class="relative bg-white rounded-3xl p-8 shadow-sm hover:shadow-lg transition-shadow text-center group">
        <div class="<?= $step['color'] ?> w-16 h-16 rounded-2xl flex items-center justify-center mx-auto mb-5 shadow-lg group-hover:scale-110 transition-transform">
          <i data-lucide="<?= $step['icon'] ?>" class="w-8 h-8 text-white"></i>
        </div>
        <span class="text-5xl font-black text-gray-100 absolute top-4 right-6"><?= $step['step'] ?></span>
        <h3 class="text-xl font-bold text-gray-900 mb-3"><?= $step['title'] ?></h3>
        <p class="text-gray-500 leading-relaxed"><?= $step['desc'] ?></p>
      </div>
      <?php endforeach; ?>
    </div>
    <div class="text-center mt-10">
      <a href="<?= APP_URL ?>/pages/auth/register.php"
         class="inline-flex items-center gap-2 bg-indigo-600 text-white px-8 py-4 rounded-2xl font-bold text-lg hover:bg-indigo-700 transition-colors shadow-lg hover:shadow-indigo-200/50">
        Get Started Free <i data-lucide="arrow-right" class="w-5 h-5"></i>
      </a>
    </div>
  </div>
</section>

<!-- ========== FEATURES ========== -->
<section class="py-20 bg-white">
  <div class="max-w-7xl mx-auto px-6">
    <div class="text-center mb-14">
      <span class="bg-cyan-100 text-cyan-700 text-xs font-bold px-3 py-1.5 rounded-full uppercase tracking-wide">Platform Features</span>
      <h2 class="text-4xl font-black text-gray-900 mt-3 mb-3">Everything you need to succeed</h2>
      <p class="text-gray-500 max-w-xl mx-auto">We have thought of everything so you can focus on learning.</p>
    </div>
    <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 gap-6">
      <?php foreach ([
        ['icon'=>'zap',           'title'=>'LearnPulse AI Tutor',      'color'=>'bg-indigo-500', 'light'=>'bg-indigo-50',  'desc'=>'Our AI tutor is available 24/7. Ask any tech question and get clear, instant answers powered by advanced AI.'],
        ['icon'=>'video',         'title'=>'Live Class Sessions',       'color'=>'bg-cyan-500',   'light'=>'bg-cyan-50',    'desc'=>'Join live classes with your instructor via Zoom or Google Meet. Real-time learning with real-time Q&A.'],
        ['icon'=>'award',         'title'=>'Industry Certificates',     'color'=>'bg-green-500',  'light'=>'bg-green-50',   'desc'=>'Earn verified digital certificates with QR codes upon course completion. Share on LinkedIn or with employers.'],
        ['icon'=>'trophy',        'title'=>'Gamified Learning',         'color'=>'bg-amber-500',  'light'=>'bg-amber-50',   'desc'=>'Earn points for every activity. Compete on the leaderboard and stay motivated throughout your learning journey.'],
        ['icon'=>'smartphone',    'title'=>'M-Pesa & Card Payments',    'color'=>'bg-green-600',  'light'=>'bg-green-50',   'desc'=>'Pay easily with M-Pesa, Stripe, PayPal or Paystack. Instant enrollment upon payment confirmation.'],
        ['icon'=>'message-square','title'=>'Student Community',         'color'=>'bg-purple-500', 'light'=>'bg-purple-50',  'desc'=>'Connect with fellow students in our platform-wide community chat. Network, share, and collaborate.'],
      ] as $f): ?>
      <div class="group p-6 rounded-3xl border border-gray-100 hover:border-transparent hover:shadow-xl transition-all duration-300 hover:-translate-y-1">
        <div class="w-14 h-14 <?= $f['light'] ?> rounded-2xl flex items-center justify-center mb-5 group-hover:scale-110 transition-transform">
          <i data-lucide="<?= $f['icon'] ?>" class="w-7 h-7 <?= str_replace('bg-','text-',$f['color']) ?>"></i>
        </div>
        <h3 class="text-lg font-bold text-gray-900 mb-2"><?= $f['title'] ?></h3>
        <p class="text-gray-500 text-sm leading-relaxed"><?= $f['desc'] ?></p>
      </div>
      <?php endforeach; ?>
    </div>
  </div>
</section>

<!-- ========== FEATURED COURSES ========== -->
<?php if (!empty($courses)): ?>
<section id="courses" class="py-20 bg-gray-50">
  <div class="max-w-7xl mx-auto px-6">
    <div class="flex items-end justify-between mb-12">
      <div>
        <span class="bg-indigo-100 text-indigo-700 text-xs font-bold px-3 py-1.5 rounded-full uppercase tracking-wide">Courses</span>
        <h2 class="text-4xl font-black text-gray-900 mt-3">Featured Courses</h2>
        <p class="text-gray-500 mt-2">Taught by industry professionals with real-world experience</p>
      </div>
      <a href="<?= APP_URL ?>/pages/public/courses.php"
         class="hidden sm:flex items-center gap-2 bg-white border border-gray-200 text-gray-700 px-5 py-3 rounded-xl font-medium hover:border-indigo-400 hover:text-indigo-600 transition-colors text-sm">
        All courses <i data-lucide="arrow-right" class="w-4 h-4"></i>
      </a>
    </div>
    <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 gap-6">
      <?php foreach ($courses as $c):
        $dc = match($c['difficulty']) { 'beginner'=>'bg-green-500','intermediate'=>'bg-amber-500',default=>'bg-red-500' };
      ?>
      <div class="bg-white rounded-3xl overflow-hidden shadow-sm hover:shadow-xl transition-all hover:-translate-y-1 duration-300">
        <div class="relative h-48">
          <?php if ($c['thumbnail']): ?>
          <img src="<?= htmlspecialchars($c['thumbnail']) ?>" class="w-full h-full object-cover" />
          <?php else: ?>
          <div class="w-full h-full bg-gradient-to-br from-indigo-600 to-cyan-500 flex items-center justify-center">
            <i data-lucide="book-open" class="w-12 h-12 text-white opacity-40"></i>
          </div>
          <?php endif; ?>
          <div class="absolute inset-0 bg-gradient-to-t from-black/40 to-transparent"></div>
          <span class="absolute top-3 left-3 text-xs font-bold px-3 py-1.5 rounded-full text-white <?= $dc ?>"><?= ucfirst($c['difficulty']) ?></span>
          <?php if ($c['price_kes'] == 0): ?>
          <span class="absolute top-3 right-3 bg-green-500 text-white text-xs font-bold px-3 py-1.5 rounded-full">FREE</span>
          <?php endif; ?>
          <div class="absolute bottom-3 left-3 flex items-center gap-2">
            <span class="bg-black/50 backdrop-blur-sm text-white text-xs px-2 py-1 rounded-full flex items-center gap-1">
              <i data-lucide="users" class="w-3 h-3"></i> <?= $c['enrolled_count'] ?>
            </span>
          </div>
        </div>
        <div class="p-5">
          <p class="text-xs text-indigo-600 font-semibold mb-1"><?= htmlspecialchars($c['category']) ?></p>
          <h3 class="font-bold text-gray-900 text-base mb-1 leading-snug"><?= htmlspecialchars($c['name']) ?></h3>
          <?php if ($c['lecturer_name']): ?>
          <p class="text-xs text-gray-400 mb-4 flex items-center gap-1">
            <i data-lucide="user" class="w-3 h-3"></i> <?= htmlspecialchars($c['lecturer_name']) ?>
          </p>
          <?php else: ?>
          <div class="mb-4"></div>
          <?php endif; ?>
          <div class="flex items-center justify-between">
            <?php if ($c['price_kes'] == 0): ?>
            <span class="text-xl font-black text-green-600">Free</span>
            <?php else: ?>
            <div>
              <span class="text-xl font-black text-indigo-600">KES <?= number_format($c['price_kes'],0) ?></span>
              <span class="text-xs text-gray-400 ml-1">/ USD <?= number_format($c['price_usd'],2) ?></span>
            </div>
            <?php endif; ?>
            <a href="<?= APP_URL ?>/pages/auth/register.php"
               class="bg-indigo-600 text-white px-4 py-2 rounded-xl text-sm font-semibold hover:bg-indigo-700 transition-colors flex items-center gap-1.5">
              Enroll <i data-lucide="arrow-right" class="w-3.5 h-3.5"></i>
            </a>
          </div>
        </div>
      </div>
      <?php endforeach; ?>
    </div>
    <div class="text-center mt-10">
      <a href="<?= APP_URL ?>/pages/public/courses.php"
         class="inline-flex items-center gap-2 border-2 border-indigo-600 text-indigo-600 px-8 py-3.5 rounded-2xl font-bold hover:bg-indigo-600 hover:text-white transition-all">
        View All <?= $total_courses ?> Courses <i data-lucide="arrow-right" class="w-4 h-4"></i>
      </a>
    </div>
  </div>
</section>
<?php endif; ?>

<!-- ========== TESTIMONIALS ========== -->
<section class="py-20 bg-white">
  <div class="max-w-6xl mx-auto px-6">
    <div class="text-center mb-12">
      <span class="bg-green-100 text-green-700 text-xs font-bold px-3 py-1.5 rounded-full uppercase tracking-wide">Testimonials</span>
      <h2 class="text-4xl font-black text-gray-900 mt-3 mb-3">What our students say</h2>
    </div>
    <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
      <?php foreach ([
        ['name'=>'Amina Wanjiru',  'role'=>'IT Support Specialist', 'text'=>'ByteClass completely changed my career trajectory. The IT Support course was practical, well-structured, and the AI tutor was incredibly helpful whenever I got stuck.',  'avatar'=>'AW', 'color'=>'bg-indigo-500'],
        ['name'=>'David Omondi',   'role'=>'Junior Cybersecurity Analyst','text'=>'The cybersecurity course on ByteClass is world-class. I landed my first job within 3 months of completing the course. The certificate carries real weight.', 'avatar'=>'DO', 'color'=>'bg-cyan-500'],
        ['name'=>'Grace Mutua',    'role'=>'Network Engineer',      'text'=>'I love how the platform tracks my progress and awards points. It kept me motivated. The live sessions with the lecturer were an amazing bonus!',                              'avatar'=>'GM', 'color'=>'bg-green-500'],
      ] as $t): ?>
      <div class="bg-gray-50 rounded-3xl p-6 hover:shadow-md transition-shadow">
        <div class="flex items-center gap-1 mb-4">
          <?php for($i=0;$i<5;$i++): ?><i data-lucide="star" class="w-4 h-4 text-amber-400 fill-amber-400"></i><?php endfor; ?>
        </div>
        <p class="text-gray-700 leading-relaxed mb-5 text-sm">"<?= $t['text'] ?>"</p>
        <div class="flex items-center gap-3">
          <div class="w-10 h-10 <?= $t['color'] ?> rounded-full flex items-center justify-center text-white font-bold text-sm flex-shrink-0">
            <?= $t['avatar'] ?>
          </div>
          <div>
            <p class="font-semibold text-gray-900 text-sm"><?= $t['name'] ?></p>
            <p class="text-gray-400 text-xs"><?= $t['role'] ?></p>
          </div>
        </div>
      </div>
      <?php endforeach; ?>
    </div>
  </div>
</section>

<!-- ========== AI TUTOR SECTION ========== -->
<section class="py-20 bg-gradient-to-br from-indigo-900 to-cyan-900 relative overflow-hidden">
  <div class="absolute inset-0 opacity-10">
    <img src="https://images.unsplash.com/photo-1677442135703-1787eea5ce01?w=1600&q=70&auto=format&fit=crop"
      class="w-full h-full object-cover" />
  </div>
  <div class="relative max-w-6xl mx-auto px-6 grid grid-cols-1 lg:grid-cols-2 gap-12 items-center">
    <div>
      <div class="inline-flex items-center gap-2 bg-white/10 border border-white/20 rounded-full px-4 py-2 mb-6">
        <i data-lucide="zap" class="w-4 h-4 text-cyan-400"></i>
        <span class="text-white text-sm font-medium">Powered by Gemini AI</span>
      </div>
      <h2 class="text-4xl font-black text-white mb-4">Meet LearnPulse<br><span class="text-cyan-400">Your AI Tutor</span></h2>
      <p class="text-indigo-200 text-lg leading-relaxed mb-6">
        Stuck on a concept at 2am? LearnPulse is always online. Ask any question about your course content, get code explanations, study tips, and more.
      </p>
      <div class="space-y-3 mb-8">
        <?php foreach ([
          'Available 24 hours a day, 7 days a week',
          'Understands all ByteClass course topics',
          'Explains concepts in simple, clear language',
          'Helps debug code and solve technical problems',
        ] as $item): ?>
        <div class="flex items-center gap-3">
          <div class="w-6 h-6 bg-cyan-400/20 rounded-full flex items-center justify-center flex-shrink-0">
            <i data-lucide="check" class="w-3.5 h-3.5 text-cyan-400"></i>
          </div>
          <p class="text-indigo-200 text-sm"><?= $item ?></p>
        </div>
        <?php endforeach; ?>
      </div>
      <a href="<?= APP_URL ?>/pages/auth/register.php"
         class="inline-flex items-center gap-2 bg-white text-indigo-900 px-6 py-3.5 rounded-xl font-bold hover:bg-indigo-50 transition-colors">
        Try LearnPulse Free <i data-lucide="arrow-right" class="w-4 h-4"></i>
      </a>
    </div>
    <!-- Chat preview -->
    <div class="bg-white/10 backdrop-blur-sm border border-white/20 rounded-3xl p-5 shadow-2xl">
      <div class="bg-gradient-to-r from-indigo-600 to-cyan-500 rounded-2xl px-4 py-3 mb-4 flex items-center gap-3">
        <div class="w-8 h-8 bg-white/20 rounded-xl flex items-center justify-center">
          <i data-lucide="zap" class="w-4 h-4 text-white"></i>
        </div>
        <div>
          <p class="text-white text-sm font-semibold">LearnPulse AI</p>
          <p class="text-indigo-200 text-xs">Always online · Instant answers</p>
        </div>
      </div>
      <div class="space-y-3">
        <div class="flex gap-2">
          <div class="w-7 h-7 bg-indigo-500 rounded-full flex items-center justify-center text-white text-xs font-bold flex-shrink-0">J</div>
          <div class="bg-gray-100 rounded-2xl rounded-tl-sm px-3 py-2 max-w-48 text-sm text-gray-800">What is a firewall and why do we need one?</div>
        </div>
        <div class="flex gap-2 flex-row-reverse">
          <div class="w-7 h-7 bg-gradient-to-br from-indigo-500 to-cyan-500 rounded-full flex items-center justify-center flex-shrink-0">
            <i data-lucide="zap" class="w-3.5 h-3.5 text-white"></i>
          </div>
          <div class="bg-indigo-600 text-white rounded-2xl rounded-tr-sm px-3 py-2 max-w-52 text-sm leading-relaxed">
            Great question! A firewall is like a security guard for your network. It monitors traffic and blocks suspicious connections. Think of it as a bouncer at a club — only trusted guests get in! 🔒
          </div>
        </div>
        <div class="flex gap-2">
          <div class="w-7 h-7 bg-indigo-500 rounded-full flex items-center justify-center text-white text-xs font-bold flex-shrink-0">J</div>
          <div class="bg-gray-100 rounded-2xl rounded-tl-sm px-3 py-2 text-sm text-gray-800">That makes sense! What are the types?</div>
        </div>
        <div class="flex items-center gap-2 px-2">
          <div class="w-2 h-2 bg-indigo-400 rounded-full animate-bounce"></div>
          <div class="w-2 h-2 bg-indigo-400 rounded-full animate-bounce" style="animation-delay:0.15s"></div>
          <div class="w-2 h-2 bg-indigo-400 rounded-full animate-bounce" style="animation-delay:0.3s"></div>
        </div>
      </div>
    </div>
  </div>
</section>

<!-- ========== CTA ========== -->
<section class="py-20 bg-indigo-600 relative overflow-hidden">
  <div class="absolute inset-0 bg-gradient-to-r from-indigo-600 to-cyan-600"></div>
  <div class="absolute top-0 left-0 w-64 h-64 bg-white/5 rounded-full -translate-x-1/2 -translate-y-1/2"></div>
  <div class="absolute bottom-0 right-0 w-96 h-96 bg-white/5 rounded-full translate-x-1/3 translate-y-1/3"></div>
  <div class="relative max-w-4xl mx-auto px-6 text-center">
    <h2 class="text-4xl md:text-5xl font-black text-white mb-4">Ready to transform your tech career?</h2>
    <p class="text-indigo-200 text-xl mb-10">Join thousands of students who are building the future with ByteClass.</p>
    <div class="flex flex-col sm:flex-row gap-4 justify-center">
      <a href="<?= APP_URL ?>/pages/auth/register.php"
         class="bg-white text-indigo-600 px-10 py-5 rounded-2xl font-black text-lg hover:bg-indigo-50 transition-all shadow-2xl hover:scale-105 flex items-center justify-center gap-3">
        <i data-lucide="user-plus" class="w-6 h-6"></i>
        Create Free Account
      </a>
      <a href="<?= APP_URL ?>/pages/public/courses.php"
         class="border-2 border-white/40 text-white px-10 py-5 rounded-2xl font-black text-lg hover:bg-white/10 transition-all flex items-center justify-center gap-3">
        <i data-lucide="book-open" class="w-6 h-6"></i>
        Browse Courses
      </a>
    </div>
  </div>
</section>

<?php require_once $_SERVER['DOCUMENT_ROOT'] . '/ByteClass/includes/footer.php'; ?>

