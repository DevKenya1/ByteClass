<?php
ob_start(); // ✅ Start output buffering – fixes header error
require_once $_SERVER['DOCUMENT_ROOT'] . '/ByteClass/config/constants.php';
require_once $_SERVER['DOCUMENT_ROOT'] . '/ByteClass/config/session.php';
$page_title = 'FAQ — ByteClass';
require_once $_SERVER['DOCUMENT_ROOT'] . '/ByteClass/includes/head.php';
require_once $_SERVER['DOCUMENT_ROOT'] . '/ByteClass/includes/navbar.php';

$faqs = [
  'Getting Started' => [
    ['q'=>'Is ByteClass free to join?', 'a'=>'Yes! Creating a ByteClass account is completely free. You can browse all courses, access free courses, and use basic platform features at no cost. Paid courses require enrollment payment.'],
    ['q'=>'Do I need any prior tech experience?', 'a'=>'Not at all! We have courses for complete beginners as well as advanced learners. Each course clearly indicates its difficulty level (Beginner, Intermediate, Advanced) so you can choose the right starting point.'],
    ['q'=>'How do I register?', 'a'=>'Click "Get Started" on any page, fill in your name, email, phone, address and create a password. You will receive a verification email — click the link to activate your account and start learning immediately.'],
    ['q'=>'Can I access ByteClass on my phone?', 'a'=>'Yes! ByteClass is fully responsive and works on smartphones, tablets and computers. Your progress syncs across all devices automatically.'],
  ],
  'Courses & Learning' => [
    ['q'=>'How are the courses structured?', 'a'=>'Courses are organized into modules, and each module contains lessons. Lessons include video content, written material, and quizzes. You progress through them in order, and each completed lesson awards you points.'],
    ['q'=>'Are there live classes?', 'a'=>'Yes! Many courses include live class sessions conducted via Zoom or Google Meet. These are scheduled by your lecturer and you will be notified in advance. You can see upcoming sessions on your dashboard.'],
    ['q'=>'What is LearnPulse AI?', 'a'=>'LearnPulse is our built-in AI tutor powered by Google Gemini. It is available 24/7 and can answer any question about your courses, explain difficult concepts, help debug code, and provide study tips — all in conversational language.'],
    ['q'=>'How long do I have access to a course?', 'a'=>'Once enrolled, you have lifetime access to the course materials. You can revisit lessons as many times as you need, even after completing the course.'],
    ['q'=>'What happens if I fail a quiz?', 'a'=>'No worries! You can retake quizzes. Your attempts are recorded so you can track your improvement. Points are awarded when you pass, so keep trying!'],
  ],
  'Certificates & Points' => [
    ['q'=>'How do I earn a certificate?', 'a'=>'To earn a certificate, you must complete all lessons and pass all quizzes in a course. Once you complete a course, a digital certificate with a unique QR code is automatically generated. You can download it as a PDF.'],
    ['q'=>'Are ByteClass certificates recognized by employers?', 'a'=>'Our certificates are industry-aligned and verified with unique QR codes that employers can scan to verify authenticity. We are continuously building relationships with tech companies and employers across Kenya.'],
    ['q'=>'What is the points system?', 'a'=>'You earn points for various activities: +50 for daily login, +100 for completing a lesson, +100 for passing a quiz, +500 for completing a module, +500 for enrolling in a course, and +1000 for completing a full course. Points determine your position on the leaderboard.'],
    ['q'=>'What do points do for me?', 'a'=>'Points rank you on the student leaderboard, which is visible to all students and potential employers. High-ranking students may receive recognition, special announcements, and future benefits as we grow the platform.'],
  ],
  'Payments & Refunds' => [
    ['q'=>'What payment methods are accepted?', 'a'=>'We accept M-Pesa (Kenya), Stripe (Visa/Mastercard), PayPal, and Paystack. The available payment methods depend on your region. M-Pesa is the most popular option for Kenyan students.'],
    ['q'=>'How does M-Pesa payment work?', 'a'=>'When you choose M-Pesa, an STK push is sent directly to your phone number. Enter your M-Pesa PIN to confirm, and your enrollment is activated instantly.'],
    ['q'=>'What is your refund policy?', 'a'=>'Once you have paid and your course access has been unlocked, refunds are not available. Please review the full course overview, difficulty level, and content carefully before enrolling in a paid course. Free courses are available to try before you commit to paid content.'],
    ['q'=>'Is my payment information secure?', 'a'=>'Absolutely. ByteClass does not store your card details. All payment processing is handled by our certified payment partners (Stripe, PayPal, Paystack) who comply with PCI DSS standards. M-Pesa payments go through Safaricom\'s official Daraja API.'],
    ['q'=>'I paid but cannot access the course — what do I do?', 'a'=>'This is rare but can happen due to network issues. First, refresh your browser and check "My Courses". If the course is still not there after 10 minutes, contact our support team via the Support page with your payment reference number and we will resolve it within 24 hours.'],
  ],
  'Technical Support' => [
    ['q'=>'I forgot my password — what do I do?', 'a'=>'Click "Forgot Password" on the login page, enter your email address, and we will send you a password reset link. The link expires in 24 hours. Check your spam folder if you do not see the email.'],
    ['q'=>'My account is locked — what happened?', 'a'=>'Accounts are temporarily locked after 5 consecutive failed login attempts as a security measure. Contact our support team and we will unlock your account after verifying your identity.'],
    ['q'=>'Can I change my email address?', 'a'=>'Yes. Go to your Profile settings, update your email address, and save. Your new email will be used for login going forward. Make sure you have access to the new email before changing it.'],
    ['q'=>'The video is not playing — what should I do?', 'a'=>'Try refreshing the page, clearing your browser cache, or switching browsers (Chrome recommended). If you are on a slow connection, allow the video extra time to buffer. Contact support if the issue persists.'],
    ['q'=>'How many devices can I use?', 'a'=>'You can be logged in on up to 2 devices simultaneously. If you log in on a 3rd device, the oldest session will be automatically logged out.'],
  ],
];
?>

<!-- Hero -->
<section class="relative py-24 bg-gray-900 overflow-hidden">
  <div class="absolute inset-0">
    <img src="https://images.unsplash.com/photo-1434030216411-0b793f4b4173?w=1600&q=80&auto=format&fit=crop"
      class="w-full h-full object-cover opacity-25" />
    <div class="absolute inset-0 bg-gradient-to-r from-indigo-900/90 to-cyan-900/80"></div>
  </div>
  <div class="relative max-w-4xl mx-auto px-6 text-center">
    <span class="inline-block bg-white/10 border border-white/20 text-white text-xs font-bold px-4 py-2 rounded-full uppercase tracking-widest mb-5">Help Center</span>
    <h1 class="text-5xl md:text-6xl font-black text-white mb-4">Frequently Asked<br>Questions</h1>
    <p class="text-indigo-200 text-xl max-w-2xl mx-auto">Everything you need to know about ByteClass. Can't find what you're looking for?
      <a href="<?= APP_URL ?>/pages/public/contact.php" class="text-cyan-400 underline font-medium hover:text-white transition-colors">Contact us</a>.</p>
  </div>
</section>

<section class="py-16 bg-white">
  <div class="max-w-4xl mx-auto px-6">

    <!-- Category nav -->
    <div class="flex flex-wrap gap-2 mb-10 pb-6 border-b border-gray-100">
      <?php foreach (array_keys($faqs) as $i => $cat): ?>
      <a href="#cat-<?= $i ?>"
         class="px-4 py-2 bg-gray-50 border border-gray-200 rounded-xl text-sm font-medium text-gray-700 hover:border-indigo-400 hover:text-indigo-600 transition-colors">
        <?= $cat ?>
      </a>
      <?php endforeach; ?>
    </div>

    <?php foreach ($faqs as $cat => $items): ?>
    <div id="cat-<?= array_search($cat, array_keys($faqs)) ?>" class="mb-12">
      <h2 class="text-2xl font-black text-gray-900 mb-6 flex items-center gap-3">
        <span class="w-8 h-8 bg-indigo-100 rounded-xl flex items-center justify-center">
          <i data-lucide="help-circle" class="w-4 h-4 text-indigo-600"></i>
        </span>
        <?= $cat ?>
      </h2>
      <div class="space-y-3">
        <?php foreach ($items as $idx => $faq): $uid = 'faq-'.md5($faq['q']); ?>
        <div class="border border-gray-200 rounded-2xl overflow-hidden hover:border-indigo-200 transition-colors">
          <button onclick="toggleFaq('<?= $uid ?>')"
            class="w-full text-left px-6 py-4 flex items-center justify-between gap-4 hover:bg-gray-50 transition-colors">
            <span class="font-semibold text-gray-900 text-sm"><?= htmlspecialchars($faq['q']) ?></span>
            <i data-lucide="chevron-down" class="w-5 h-5 text-gray-400 flex-shrink-0 transition-transform" id="<?= $uid ?>-icon"></i>
          </button>
          <div id="<?= $uid ?>" class="hidden px-6 pb-5">
            <p class="text-gray-600 text-sm leading-relaxed"><?= htmlspecialchars($faq['a']) ?></p>
          </div>
        </div>
        <?php endforeach; ?>
      </div>
    </div>
    <?php endforeach; ?>

    <!-- Still need help? -->
    <div class="bg-indigo-50 border border-indigo-200 rounded-3xl p-8 text-center mt-6">
      <i data-lucide="help-circle" class="w-12 h-12 text-indigo-600 mx-auto mb-3"></i>
      <h3 class="text-xl font-bold text-gray-900 mb-2">Still have questions?</h3>
      <p class="text-gray-500 mb-5">Our support team is here to help. Reach out and we will get back to you quickly.</p>
      <div class="flex flex-wrap gap-3 justify-center">
        <a href="<?= APP_URL ?>/pages/public/contact.php"
           class="bg-indigo-600 text-white px-6 py-3 rounded-xl font-semibold text-sm hover:bg-indigo-700 transition-colors flex items-center gap-2">
          <i data-lucide="mail" class="w-4 h-4"></i> Contact Support
        </a>
        <?php if (!empty($_SESSION['user_id'])): ?>
        <a href="<?= APP_URL ?>/<?= $_SESSION['role'] ?>/support.php"
           class="bg-white border border-gray-200 text-gray-700 px-6 py-3 rounded-xl font-semibold text-sm hover:border-indigo-400 transition-colors flex items-center gap-2">
          <i data-lucide="headphones" class="w-4 h-4"></i> Open a Ticket
        </a>
        <?php endif; ?>
      </div>
    </div>
  </div>
</section>

<?php require_once $_SERVER['DOCUMENT_ROOT'] . '/ByteClass/includes/footer.php'; ?>
<script>
function toggleFaq(id) {
  const el   = document.getElementById(id);
  const icon = document.getElementById(id+'-icon');
  const open = !el.classList.contains('hidden');
  el.classList.toggle('hidden', open);
  icon.style.transform = open ? '' : 'rotate(180deg)';
}
</script>
<?php ob_end_flush(); // ✅ Flush output buffer after all content ?>
