<?php
$required_role = 'student';
require_once $_SERVER['DOCUMENT_ROOT'] . '/ByteClass/includes/auth-check.php';
require_once $_SERVER['DOCUMENT_ROOT'] . '/ByteClass/config/constants.php';
require_once $_SERVER['DOCUMENT_ROOT'] . '/ByteClass/config/database.php';
require_once $_SERVER['DOCUMENT_ROOT'] . '/ByteClass/helpers/response.php';

$db        = Database::getInstance()->getConnection();
$id        = (int)$_SESSION['user_id'];
$course_id = (int)($_GET['course_id'] ?? 0);

if (!$course_id) { header('Location: ' . APP_URL . '/student/explore.php'); exit; }

// Get course
$stmt = $db->prepare("SELECT * FROM courses WHERE id=? AND status='published' LIMIT 1");
$stmt->execute([$course_id]);
$course = $stmt->fetch();
if (!$course) { header('Location: ' . APP_URL . '/student/explore.php'); exit; }

// Already enrolled?
$check = $db->prepare("SELECT id FROM enrollments WHERE student_id=? AND course_id=? LIMIT 1");
$check->execute([$id, $course_id]);
if ($check->fetch()) { header('Location: ' . APP_URL . '/student/courses.php'); exit; }

// Free course — should have been caught in enroll.php but just in case
if ($course['price_kes'] == 0) {
    header('Location: ' . APP_URL . '/student/enroll.php?course_id=' . $course_id); exit;
}

// Get enabled gateways
$gateways = [];
foreach (['mpesa','stripe','paypal','paystack'] as $gw) {
    $en = $db->query("SELECT setting_value FROM system_settings WHERE setting_key='{$gw}_enabled'")->fetchColumn();
    if ($en === '1') $gateways[] = $gw;
}

// Handle POST — simulate payment intent creation
$error_msg = '';
$success_msg = '';
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $gateway = sanitize($_POST['gateway'] ?? '');
    if (!in_array($gateway, $gateways)) {
        $error_msg = 'Invalid payment gateway selected.';
    } else {
        // Create a pending payment record
        $receipt = strtoupper($gateway) . '-' . strtoupper(substr(bin2hex(random_bytes(6)),0,10));
        $db->prepare("INSERT INTO payments (student_id, course_id, amount, currency, gateway, status, receipt_id, initiated_at)
                      VALUES (?,?,?,?,?,'pending',?,NOW())")
           ->execute([$id, $course_id, $course['price_kes'], 'KES', $gateway, $receipt]);
        $payment_id = (int)$db->lastInsertId();

        // In real implementation, redirect to gateway here
        // For now — show pending message with gateway instructions
        $session_data = ['payment_id'=>$payment_id, 'gateway'=>$gateway, 'receipt'=>$receipt, 'course_id'=>$course_id];
        $_SESSION['pending_payment'] = $session_data;

        // Redirect to payment gateway simulation page
        header('Location: ' . APP_URL . '/student/payment-gateway.php?id=' . $payment_id);
        exit;
    }
}

$page_title = 'Enroll — ' . $course['name'];
require_once $_SERVER['DOCUMENT_ROOT'] . '/ByteClass/includes/head.php';
?>
<div class="flex min-h-screen bg-gray-50">
  <?php require_once $_SERVER['DOCUMENT_ROOT'] . '/ByteClass/includes/sidebar-student.php'; ?>
  <div id="main-content" class="flex-1 flex flex-col" style="margin-left:256px;">
    <?php require_once $_SERVER['DOCUMENT_ROOT'] . '/ByteClass/includes/navbar-student.php'; ?>
    <main class="flex-1 p-6 max-w-2xl">

      <div class="bg-gradient-to-r from-indigo-600 to-cyan-500 rounded-2xl px-6 py-5 mb-6">
        <h2 class="text-white text-xl font-bold">Complete Enrollment</h2>
        <p class="text-indigo-100 text-sm mt-0.5">Choose your payment method to unlock this course</p>
      </div>

      <?php if ($error_msg): ?>
      <div class="mb-4 p-4 bg-red-50 border border-red-200 rounded-xl text-sm text-red-700 flex gap-3">
        <i data-lucide="alert-circle" class="w-5 h-5 flex-shrink-0"></i><?= htmlspecialchars($error_msg) ?>
      </div>
      <?php endif; ?>

      <!-- Course summary -->
      <div class="bg-white rounded-2xl border border-gray-100 p-5 mb-6 flex gap-4">
        <div class="w-20 h-16 rounded-xl overflow-hidden flex-shrink-0">
          <?php if ($course['thumbnail']): ?>
          <img src="<?= htmlspecialchars($course['thumbnail']) ?>" class="w-full h-full object-cover" />
          <?php else: ?>
          <div class="w-full h-full bg-gradient-to-br from-indigo-600 to-cyan-500 flex items-center justify-center">
            <i data-lucide="book-open" class="w-6 h-6 text-white opacity-60"></i>
          </div>
          <?php endif; ?>
        </div>
        <div class="flex-1">
          <h3 class="font-semibold text-gray-900 text-sm mb-0.5"><?= htmlspecialchars($course['name']) ?></h3>
          <p class="text-xs text-gray-400 mb-2"><?= htmlspecialchars($course['category']) ?> · <?= ucfirst($course['difficulty']) ?></p>
          <div class="flex items-center gap-3">
            <span class="text-xl font-bold text-indigo-600">KES <?= number_format($course['price_kes'],2) ?></span>
            <span class="text-sm text-gray-400">/ USD <?= number_format($course['price_usd'],2) ?></span>
          </div>
        </div>
      </div>

      <!-- No refund notice -->
      <div class="bg-amber-50 border border-amber-200 rounded-xl p-4 mb-6 flex gap-3">
        <i data-lucide="info" class="w-5 h-5 text-amber-600 flex-shrink-0"></i>
        <div>
          <p class="text-sm font-semibold text-amber-800">No refund policy</p>
          <p class="text-xs text-amber-700 mt-0.5">Once payment is confirmed and course access is unlocked, refunds are not available. Please review the course overview before purchasing.</p>
        </div>
      </div>

      <!-- Payment gateways -->
      <?php if (empty($gateways)): ?>
      <div class="bg-white rounded-2xl border border-gray-100 p-8 text-center">
        <i data-lucide="credit-card" class="w-10 h-10 text-gray-300 mx-auto mb-3"></i>
        <p class="text-gray-500 font-medium">No payment methods available</p>
        <p class="text-gray-400 text-sm mt-1">Payment gateways have not been configured. Please contact the administrator.</p>
      </div>
      <?php else: ?>
      <form method="POST" class="space-y-3">
        <h3 class="font-semibold text-gray-900 mb-3">Select payment method</h3>
        <?php
        $gw_info = [
          'mpesa'   => ['label'=>'M-Pesa', 'icon'=>'smartphone',   'color'=>'bg-green-50 border-green-200',   'badge'=>'bg-green-500', 'desc'=>'Pay via M-Pesa STK push — instant confirmation'],
          'stripe'  => ['label'=>'Stripe',  'icon'=>'credit-card', 'color'=>'bg-blue-50 border-blue-200',     'badge'=>'bg-blue-500',  'desc'=>'Pay with Visa, Mastercard or any debit card'],
          'paypal'  => ['label'=>'PayPal',  'icon'=>'globe',       'color'=>'bg-sky-50 border-sky-200',       'badge'=>'bg-sky-500',   'desc'=>'Pay with your PayPal account or card'],
          'paystack'=> ['label'=>'Paystack','icon'=>'zap',         'color'=>'bg-indigo-50 border-indigo-200', 'badge'=>'bg-indigo-500','desc'=>'Pay with card, bank transfer or mobile money'],
        ];
        foreach ($gateways as $gw):
          $info = $gw_info[$gw] ?? ['label'=>strtoupper($gw),'icon'=>'credit-card','color'=>'bg-gray-50 border-gray-200','badge'=>'bg-gray-500','desc'=>''];
        ?>
        <label class="flex items-center gap-4 p-4 bg-white border-2 rounded-2xl cursor-pointer hover:border-indigo-400 transition-colors has-[:checked]:border-indigo-600 has-[:checked]:bg-indigo-50">
          <input type="radio" name="gateway" value="<?= $gw ?>" class="w-4 h-4 text-indigo-600" required />
          <div class="w-10 h-10 <?= $info['badge'] ?> rounded-xl flex items-center justify-center flex-shrink-0">
            <i data-lucide="<?= $info['icon'] ?>" class="w-5 h-5 text-white"></i>
          </div>
          <div class="flex-1">
            <p class="font-semibold text-gray-900 text-sm"><?= $info['label'] ?></p>
            <p class="text-xs text-gray-500"><?= $info['desc'] ?></p>
          </div>
        </label>
        <?php endforeach; ?>

        <div class="pt-4">
          <button type="submit"
            class="w-full bg-indigo-600 text-white py-4 rounded-2xl font-bold text-base hover:bg-indigo-700 transition-colors flex items-center justify-center gap-3">
            <i data-lucide="lock" class="w-5 h-5"></i>
            Pay KES <?= number_format($course['price_kes'],2) ?> &amp; Unlock Course
          </button>
          <p class="text-xs text-gray-400 text-center mt-3 flex items-center justify-center gap-1">
            <i data-lucide="shield" class="w-3 h-3"></i>
            Secured payment · Your card info is never stored on ByteClass
          </p>
        </div>
      </form>
      <?php endif; ?>

      <div class="mt-6 text-center">
        <a href="<?= APP_URL ?>/student/explore.php"
           class="text-sm text-gray-400 hover:text-gray-600 flex items-center justify-center gap-1">
          <i data-lucide="arrow-left" class="w-4 h-4"></i> Back to courses
        </a>
      </div>

    </main>
    <?php require_once $_SERVER['DOCUMENT_ROOT'] . '/ByteClass/includes/footer-minimal.php'; ?>
  </div>
</div>
<?php require_once $_SERVER['DOCUMENT_ROOT'] . '/ByteClass/includes/scripts.php'; ?>
