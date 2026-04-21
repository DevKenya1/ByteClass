<?php
require_once $_SERVER['DOCUMENT_ROOT'] . '/ByteClass/config/constants.php';
require_once $_SERVER['DOCUMENT_ROOT'] . '/ByteClass/config/session.php';
require_once $_SERVER['DOCUMENT_ROOT'] . '/ByteClass/config/database.php';
require_once $_SERVER['DOCUMENT_ROOT'] . '/ByteClass/helpers/response.php';
require_once $_SERVER['DOCUMENT_ROOT'] . '/ByteClass/helpers/mailer.php';

$success = $error = '';
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $name    = sanitize($_POST['name']    ?? '');
    $email   = sanitize($_POST['email']   ?? '');
    $subject = sanitize($_POST['subject'] ?? '');
    $message = sanitize($_POST['message'] ?? '');
    if (!$name || !$email || !$subject || !$message) {
        $error = 'All fields are required.';
    } elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $error = 'Please enter a valid email address.';
    } else {
        $db = Database::getInstance()->getConnection();
        $db->prepare("INSERT INTO activity_logs (user_id,action,description,ip_address) VALUES (NULL,'contact_form',?,?)")
           ->execute(["Contact form: $name ($email) — $subject", $_SERVER['REMOTE_ADDR']??'']);
        // Send notification to admin
        $html = email_template('New Contact Form Message', "
            <p>You have received a new message from the ByteClass contact form.</p>
            <table>
              <tr><td><strong>Name:</strong></td><td>".htmlspecialchars($name)."</td></tr>
              <tr><td><strong>Email:</strong></td><td>".htmlspecialchars($email)."</td></tr>
              <tr><td><strong>Subject:</strong></td><td>".htmlspecialchars($subject)."</td></tr>
            </table>
            <div style='background:#f8fafc;border-radius:8px;padding:16px;margin-top:16px;'>
              <p style='color:#475569;'>".nl2br(htmlspecialchars($message))."</p>
            </div>
        ");
        $admin_email = $db->query("SELECT setting_value FROM system_settings WHERE setting_key='platform_email'")->fetchColumn() ?: '';
        if ($admin_email) send_email($admin_email, 'ByteClass Admin', "Contact: $subject", $html);
        $success = 'Thank you for reaching out! We will get back to you within 24 hours.';
    }
}

$page_title = 'Contact Us — ByteClass';
require_once $_SERVER['DOCUMENT_ROOT'] . '/ByteClass/includes/head.php';
require_once $_SERVER['DOCUMENT_ROOT'] . '/ByteClass/includes/navbar.php';
?>

<section class="relative py-24 bg-gray-900 overflow-hidden">
  <div class="absolute inset-0"><img src="https://images.unsplash.com/photo-1423666639041-f56000c27a9a?w=1600&q=80&auto=format&fit=crop" class="w-full h-full object-cover opacity-20" /><div class="absolute inset-0 bg-gradient-to-r from-indigo-900/90 to-cyan-900/80"></div></div><div class="relative max-w-4xl mx-auto px-6 text-center">
    <h1 class="text-5xl font-black text-white mb-4">Contact Us</h1>
    <p class="text-indigo-200 text-xl">We would love to hear from you. Our team is here to help.</p>
  </div>
</section>

<section class="py-16 bg-gray-50">
  <div class="max-w-6xl mx-auto px-6 grid grid-cols-1 lg:grid-cols-3 gap-12">

    <!-- Contact info -->
    <div class="space-y-6">
      <h2 class="text-2xl font-bold text-gray-900">Get in touch</h2>
      <p class="text-gray-500 leading-relaxed">Have a question, feedback, or want to partner with us? Fill out the form and we will respond within 24 hours.</p>
      <?php foreach ([
        ['icon'=>'mail',      'title'=>'Email',    'value'=>'support@byteclass.io',    'link'=>'mailto:support@byteclass.io'],
        ['icon'=>'phone',     'title'=>'Phone',    'value'=>'+254 700 000 000',         'link'=>'tel:+254700000000'],
        ['icon'=>'map-pin',   'title'=>'Location', 'value'=>'Nairobi, Kenya',           'link'=>'#'],
        ['icon'=>'clock',     'title'=>'Hours',    'value'=>'Mon–Fri, 8am–6pm EAT',    'link'=>'#'],
      ] as $info): ?>
      <div class="flex items-start gap-4">
        <div class="w-10 h-10 bg-indigo-100 rounded-xl flex items-center justify-center flex-shrink-0 mt-0.5">
          <i data-lucide="<?= $info['icon'] ?>" class="w-5 h-5 text-indigo-600"></i>
        </div>
        <div>
          <p class="font-semibold text-gray-900 text-sm"><?= $info['title'] ?></p>
          <a href="<?= $info['link'] ?>" class="text-gray-500 text-sm hover:text-indigo-600 transition-colors"><?= $info['value'] ?></a>
        </div>
      </div>
      <?php endforeach; ?>

      <div class="pt-4 border-t border-gray-200">
        <p class="text-sm font-semibold text-gray-700 mb-3">Quick Links</p>
        <div class="space-y-2">
          <a href="<?= APP_URL ?>/pages/public/faq.php" class="flex items-center gap-2 text-sm text-gray-500 hover:text-indigo-600"><i data-lucide="help-circle" class="w-4 h-4"></i> Browse FAQs</a>
          <a href="<?= APP_URL ?>/pages/auth/register.php" class="flex items-center gap-2 text-sm text-gray-500 hover:text-indigo-600"><i data-lucide="user-plus" class="w-4 h-4"></i> Create Account</a>
          <a href="<?= APP_URL ?>/pages/public/courses.php" class="flex items-center gap-2 text-sm text-gray-500 hover:text-indigo-600"><i data-lucide="book-open" class="w-4 h-4"></i> Browse Courses</a>
        </div>
      </div>
    </div>

    <!-- Form -->
    <div class="lg:col-span-2 bg-white rounded-3xl shadow-sm border border-gray-100 p-8">
      <?php if ($success): ?>
      <div class="text-center py-12">
        <div class="w-16 h-16 bg-green-100 rounded-full flex items-center justify-center mx-auto mb-4">
          <i data-lucide="check-circle" class="w-8 h-8 text-green-600"></i>
        </div>
        <h3 class="text-xl font-bold text-gray-900 mb-2">Message sent!</h3>
        <p class="text-gray-500 mb-6"><?= htmlspecialchars($success) ?></p>
        <a href="<?= APP_URL ?>/pages/public/contact.php" class="text-indigo-600 font-medium hover:underline">Send another message</a>
      </div>
      <?php else: ?>
      <?php if ($error): ?>
      <div class="mb-5 p-4 bg-red-50 border border-red-200 rounded-xl text-sm text-red-700 flex gap-3">
        <i data-lucide="alert-circle" class="w-5 h-5 flex-shrink-0"></i><?= htmlspecialchars($error) ?>
      </div>
      <?php endif; ?>
      <form method="POST" class="space-y-5">
        <div class="grid grid-cols-2 gap-5">
          <div>
            <label class="block text-sm font-medium text-gray-700 mb-1.5">Full name *</label>
            <input type="text" name="name" required value="<?= htmlspecialchars($_POST['name'] ?? '') ?>"
              placeholder="Your full name"
              class="w-full px-4 py-3 border border-gray-200 rounded-xl text-sm focus:outline-none focus:ring-2 focus:ring-indigo-500" />
          </div>
          <div>
            <label class="block text-sm font-medium text-gray-700 mb-1.5">Email address *</label>
            <input type="email" name="email" required value="<?= htmlspecialchars($_POST['email'] ?? '') ?>"
              placeholder="you@example.com"
              class="w-full px-4 py-3 border border-gray-200 rounded-xl text-sm focus:outline-none focus:ring-2 focus:ring-indigo-500" />
          </div>
        </div>
        <div>
          <label class="block text-sm font-medium text-gray-700 mb-1.5">Subject *</label>
          <select name="subject" class="w-full px-4 py-3 border border-gray-200 rounded-xl text-sm focus:outline-none focus:ring-2 focus:ring-indigo-500">
            <option value="">Select a topic</option>
            <option value="General Enquiry">General Enquiry</option>
            <option value="Course Question">Course Question</option>
            <option value="Payment Issue">Payment Issue</option>
            <option value="Technical Problem">Technical Problem</option>
            <option value="Partnership Opportunity">Partnership Opportunity</option>
            <option value="Lecturer Application">Lecturer Application</option>
            <option value="Other">Other</option>
          </select>
        </div>
        <div>
          <label class="block text-sm font-medium text-gray-700 mb-1.5">Message *</label>
          <textarea name="message" required rows="5" placeholder="Describe your question or concern in detail..."
            class="w-full px-4 py-3 border border-gray-200 rounded-xl text-sm focus:outline-none focus:ring-2 focus:ring-indigo-500 resize-none"><?= htmlspecialchars($_POST['message'] ?? '') ?></textarea>
        </div>
        <button type="submit"
          class="w-full bg-indigo-600 text-white py-4 rounded-xl font-bold text-base hover:bg-indigo-700 transition-colors flex items-center justify-center gap-3 shadow-lg">
          <i data-lucide="send" class="w-5 h-5"></i> Send Message
        </button>
        <p class="text-xs text-gray-400 text-center">We typically respond within 24 hours during business days.</p>
      </form>
      <?php endif; ?>
    </div>
  </div>
</section>

<?php require_once $_SERVER['DOCUMENT_ROOT'] . '/ByteClass/includes/footer.php'; ?>

