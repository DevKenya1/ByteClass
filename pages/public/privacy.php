<?php
require_once $_SERVER['DOCUMENT_ROOT'] . '/ByteClass/config/constants.php';
require_once $_SERVER['DOCUMENT_ROOT'] . '/ByteClass/config/session.php';
$page_title = 'Privacy Policy — ByteClass';
require_once $_SERVER['DOCUMENT_ROOT'] . '/ByteClass/includes/head.php';
require_once $_SERVER['DOCUMENT_ROOT'] . '/ByteClass/includes/navbar.php';
?>
<section class="relative py-20 bg-gray-900 overflow-hidden">
  <div class="absolute inset-0"><img src="https://images.unsplash.com/photo-1562564055-71e051d33c19?w=1600&q=70&auto=format&fit=crop" class="w-full h-full object-cover opacity-20"/><div class="absolute inset-0 bg-gradient-to-r from-indigo-900/90 to-gray-900/80"></div></div><div class="relative max-w-4xl mx-auto px-6 text-center"><h1 class="text-5xl font-black text-white mb-2">Privacy Policy</h1>
    <p class="text-indigo-200">Last updated: January 1, 2026</p>
  </div>
</section>
<section class="py-16 bg-white">
  <div class="max-w-4xl mx-auto px-6 prose prose-gray max-w-none">
    <div class="space-y-8 text-gray-700 text-sm leading-relaxed">

      <div class="bg-indigo-50 border border-indigo-200 rounded-2xl p-5">
        <p class="font-semibold text-indigo-900">Summary: ByteClass collects only what is necessary to provide our service, never sells your data, and gives you full control over your information.</p>
      </div>

      <div>
        <h2 class="text-xl font-bold text-gray-900 mb-3">1. Who We Are</h2>
        <p>ByteClass ("we", "us", "our") is an online education platform operated by ByteClass Ltd, registered in Nairobi, Kenya. We provide technology education services through our website at <strong><?= APP_URL ?></strong>. For privacy inquiries, contact us at <strong>privacy@byteclass.io</strong>.</p>
      </div>

      <div>
        <h2 class="text-xl font-bold text-gray-900 mb-3">2. Information We Collect</h2>
        <p class="mb-3">We collect information you provide directly to us when you:</p>
        <ul class="list-disc pl-6 space-y-2 mb-3">
          <li><strong>Create an account:</strong> Full name, email address, phone number, physical address, and password (stored as a secure hash — we never see your actual password).</li>
          <li><strong>Enroll in courses:</strong> Course enrollment records, payment transaction details, and receipt references.</li>
          <li><strong>Use the platform:</strong> Lesson progress, quiz scores, community messages, support tickets, and LearnPulse AI conversation history.</li>
          <li><strong>Upload a profile photo:</strong> Your photo is stored securely and displayed only to you and other platform users.</li>
          <li><strong>Contact us:</strong> Name, email, and message content when you submit our contact form.</li>
        </ul>
        <p class="mb-3">We also collect information automatically when you use ByteClass:</p>
        <ul class="list-disc pl-6 space-y-2">
          <li><strong>Usage data:</strong> Pages visited, features used, time spent on each lesson.</li>
          <li><strong>Device information:</strong> IP address, browser type, operating system, and device type.</li>
          <li><strong>Session data:</strong> Login times, session tokens (stored securely in our database).</li>
          <li><strong>Activity logs:</strong> Security-relevant actions such as logins, password changes, and payment transactions.</li>
        </ul>
      </div>

      <div>
        <h2 class="text-xl font-bold text-gray-900 mb-3">3. How We Use Your Information</h2>
        <p class="mb-2">We use the information we collect to:</p>
        <ul class="list-disc pl-6 space-y-2">
          <li>Create and manage your account and provide our educational services</li>
          <li>Process payments for course enrollments</li>
          <li>Send account-related emails: verification, password resets, and payment confirmations</li>
          <li>Award points, track progress, and issue certificates</li>
          <li>Operate the LearnPulse AI tutor (queries are sent to Google Gemini; please see Google's privacy policy)</li>
          <li>Improve our platform, courses, and user experience</li>
          <li>Detect and prevent fraud, unauthorized access, and security threats</li>
          <li>Respond to support tickets and inquiries</li>
          <li>Send platform announcements relevant to you (you can opt out in settings)</li>
          <li>Comply with legal obligations under Kenyan law</li>
        </ul>
      </div>

      <div>
        <h2 class="text-xl font-bold text-gray-900 mb-3">4. Data Sharing & Third Parties</h2>
        <p class="mb-3"><strong>We never sell your personal data to third parties.</strong></p>
        <p class="mb-2">We share data with third parties only in these circumstances:</p>
        <ul class="list-disc pl-6 space-y-2">
          <li><strong>Payment Processors:</strong> Safaricom (M-Pesa), Stripe, PayPal, and Paystack receive only the payment information necessary to process your transaction. They have their own privacy policies.</li>
          <li><strong>Google Gemini AI:</strong> Your LearnPulse queries are sent to Google's Gemini API to generate responses. Do not include sensitive personal information in AI queries.</li>
          <li><strong>Email Service:</strong> We use SMTP email services to deliver transactional emails. Your email address is shared only to deliver emails from ByteClass.</li>
          <li><strong>Legal Requirements:</strong> We may disclose data if required by Kenyan law, court order, or to protect the rights and safety of users.</li>
          <li><strong>Business Transfer:</strong> In the unlikely event ByteClass is acquired or merged, your data may be transferred to the new entity, and you will be notified.</li>
        </ul>
      </div>

      <div>
        <h2 class="text-xl font-bold text-gray-900 mb-3">5. Data Security</h2>
        <p class="mb-2">We take security seriously and implement the following measures:</p>
        <ul class="list-disc pl-6 space-y-2">
          <li>All passwords are hashed using bcrypt with cost factor 12 — we never store plain-text passwords</li>
          <li>Sessions use secure, randomly generated tokens stored in our database</li>
          <li>Accounts are automatically locked after 5 consecutive failed login attempts</li>
          <li>Support for optional two-factor authentication (2FA) via email OTP</li>
          <li>All data transmission uses HTTPS/TLS encryption</li>
          <li>Activity logs record all security-sensitive actions for audit purposes</li>
          <li>Payment card details are never stored on ByteClass servers</li>
        </ul>
        <p class="mt-3">Despite our best efforts, no system is 100% secure. If you discover a security vulnerability, please report it responsibly to <strong>security@byteclass.io</strong>.</p>
      </div>

      <div>
        <h2 class="text-xl font-bold text-gray-900 mb-3">6. Data Retention</h2>
        <ul class="list-disc pl-6 space-y-2">
          <li><strong>Account data:</strong> Retained for as long as your account is active. If you delete your account, your personal data is deleted within 30 days, except where required by law.</li>
          <li><strong>Payment records:</strong> Retained for 7 years in compliance with Kenyan tax and financial laws.</li>
          <li><strong>Community messages:</strong> Automatically deleted after 24 hours.</li>
          <li><strong>Activity logs:</strong> Retained for 12 months for security and audit purposes.</li>
          <li><strong>Public chat widget messages:</strong> Deleted when you close the chat window.</li>
        </ul>
      </div>

      <div>
        <h2 class="text-xl font-bold text-gray-900 mb-3">7. Your Rights</h2>
        <p class="mb-2">You have the right to:</p>
        <ul class="list-disc pl-6 space-y-2">
          <li><strong>Access:</strong> Request a copy of your personal data we hold</li>
          <li><strong>Correction:</strong> Update incorrect information via your Profile settings</li>
          <li><strong>Deletion:</strong> Request deletion of your account and personal data</li>
          <li><strong>Portability:</strong> Request your data in a portable format</li>
          <li><strong>Objection:</strong> Object to certain uses of your data</li>
        </ul>
        <p class="mt-3">To exercise these rights, email us at <strong>privacy@byteclass.io</strong>. We will respond within 30 days.</p>
      </div>

      <div>
        <h2 class="text-xl font-bold text-gray-900 mb-3">8. Cookies</h2>
        <p>ByteClass uses session cookies for authentication (to keep you logged in) and a small cookie to identify your public chat session. We do not use tracking cookies, advertising cookies, or any third-party analytics cookies. You can disable cookies in your browser, but this will prevent you from logging in.</p>
      </div>

      <div>
        <h2 class="text-xl font-bold text-gray-900 mb-3">9. Children's Privacy</h2>
        <p>ByteClass is not intended for children under the age of 16. We do not knowingly collect personal data from children under 16. If you believe a child under 16 has created an account, please contact us at <strong>privacy@byteclass.io</strong> and we will delete the account promptly.</p>
      </div>

      <div>
        <h2 class="text-xl font-bold text-gray-900 mb-3">10. Changes to This Policy</h2>
        <p>We may update this Privacy Policy periodically. When we make significant changes, we will notify you via email and display a notice on the platform. Your continued use of ByteClass after changes constitutes acceptance of the updated policy.</p>
      </div>

      <div>
        <h2 class="text-xl font-bold text-gray-900 mb-3">11. Contact</h2>
        <p>For privacy concerns, data requests, or questions about this policy, contact us at:</p>
        <div class="bg-gray-50 rounded-xl p-4 mt-3">
          <p><strong>ByteClass Ltd</strong><br>Nairobi, Kenya<br>Email: <a href="mailto:privacy@byteclass.io" class="text-indigo-600 hover:underline">privacy@byteclass.io</a></p>
        </div>
      </div>

    </div>
  </div>
</section>
<?php require_once $_SERVER['DOCUMENT_ROOT'] . '/ByteClass/includes/footer.php'; ?>

