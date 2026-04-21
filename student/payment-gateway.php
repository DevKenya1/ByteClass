<?php
$required_role = 'student';
require_once $_SERVER['DOCUMENT_ROOT'] . '/ByteClass/includes/auth-check.php';
require_once $_SERVER['DOCUMENT_ROOT'] . '/ByteClass/config/constants.php';
require_once $_SERVER['DOCUMENT_ROOT'] . '/ByteClass/config/database.php';
require_once $_SERVER['DOCUMENT_ROOT'] . '/ByteClass/helpers/response.php';
require_once $_SERVER['DOCUMENT_ROOT'] . '/ByteClass/helpers/points.php';

$db         = Database::getInstance()->getConnection();
$id         = (int)$_SESSION['user_id'];
$payment_id = (int)($_GET['id'] ?? 0);

if (!$payment_id) { header('Location: ' . APP_URL . '/student/explore.php'); exit; }

$p_stmt = $db->prepare("SELECT * FROM payments WHERE id=? AND student_id=? AND status='pending' LIMIT 1");
$p_stmt->execute([$payment_id, $id]);
$payment = $p_stmt->fetch();
if (!$payment) { header('Location: ' . APP_URL . '/student/explore.php'); exit; }

$course_stmt = $db->prepare("SELECT * FROM courses WHERE id=? LIMIT 1");
$course_stmt->execute([$payment['course_id']]);
$course = $course_stmt->fetch();

// Handle simulation
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $result = $_POST['result'] ?? '';

    if ($result === 'success') {
        // Confirm payment
        $db->prepare("UPDATE payments SET status='success', confirmed_at=NOW(), receipt_id=? WHERE id=?")
           ->execute([$payment['receipt_id'], $payment_id]);

        // Enroll student
        $db->prepare("INSERT IGNORE INTO enrollments (student_id, course_id) VALUES (?,?)")
           ->execute([$id, $payment['course_id']]);

        // Award 500 points
        award_points($id, 500, 'Enrolled in: ' . $course['name']);

        // Notify student
        $db->prepare("INSERT INTO notifications (user_id,title,message,type) VALUES (?,?,?,?)")
           ->execute([$id, 'Payment Successful! 🎉', 'Payment confirmed for ' . $course['name'] . '. You have been enrolled!', 'payment']);

        // Notify admins
        foreach ($db->query("SELECT id FROM users WHERE role='admin'")->fetchAll() as $a) {
            $db->prepare("INSERT INTO notifications (user_id,title,message,type) VALUES (?,?,?,?)")
               ->execute([$a['id'], 'New Payment Received', $payment['gateway'] . ' payment of KES ' . number_format($payment['amount'],2) . ' for ' . $course['name'], 'payment']);
        }

        $db->prepare("INSERT INTO activity_logs (user_id,action,target_type,target_id,description,ip_address) VALUES (?,?,?,?,?,?)")
           ->execute([$id, 'payment_success', 'course', $payment['course_id'], 'Payment successful via ' . $payment['gateway'], $_SERVER['REMOTE_ADDR']??'']);

        header('Location: ' . APP_URL . '/student/courses.php?enrolled=1');
        exit;

    } elseif ($result === 'fail') {
        // Mark payment as failed
        $db->prepare("UPDATE payments SET status='failed', retry_banner_active=1, retry_deadline=? WHERE id=?")
           ->execute([date('Y-m-d H:i:s', strtotime('+48 hours')), $payment_id]);

        $db->prepare("INSERT INTO notifications (user_id,title,message,type) VALUES (?,?,?,?)")
           ->execute([$id, 'Payment Failed', 'Your payment for ' . $course['name'] . ' failed. Please try again.', 'payment']);

        header('Location: ' . APP_URL . '/student/payment.php?course_id=' . $payment['course_id'] . '&failed=1');
        exit;
    }
}

$gw_colors = ['mpesa'=>'green','stripe'=>'blue','paypal'=>'sky','paystack'=>'indigo'];
$color = $gw_colors[$payment['gateway']] ?? 'indigo';

$page_title = 'Processing Payment';
require_once $_SERVER['DOCUMENT_ROOT'] . '/ByteClass/includes/head.php';
?>
<div class="min-h-screen bg-gray-50 flex items-center justify-center px-4">
  <div class="w-full max-w-md">
    <div class="text-center mb-8">
      <a href="<?= APP_URL ?>/" class="inline-block mb-4">
        <span class="text-2xl font-bold">
          <span class="text-cyan-500">Byte</span><span class="text-gray-900">Class</span>
        </span>
      </a>
    </div>

    <div class="bg-white rounded-2xl shadow-sm border border-gray-100 overflow-hidden">
      <div class="bg-gradient-to-r from-indigo-600 to-cyan-500 p-6 text-center">
        <p class="text-white text-xs font-medium opacity-80 mb-1">Processing payment via</p>
        <h2 class="text-white text-2xl font-bold uppercase"><?= htmlspecialchars($payment['gateway']) ?></h2>
      </div>
      <div class="p-6">
        <div class="bg-gray-50 rounded-xl p-4 mb-6">
          <div class="flex items-center justify-between text-sm mb-2">
            <span class="text-gray-500">Course</span>
            <span class="font-medium text-gray-900 text-right max-w-48 truncate"><?= htmlspecialchars($course['name']) ?></span>
          </div>
          <div class="flex items-center justify-between text-sm mb-2">
            <span class="text-gray-500">Amount</span>
            <span class="font-bold text-indigo-600 text-lg">KES <?= number_format($payment['amount'],2) ?></span>
          </div>
          <div class="flex items-center justify-between text-sm">
            <span class="text-gray-500">Reference</span>
            <span class="font-mono text-xs text-gray-600"><?= htmlspecialchars($payment['receipt_id']) ?></span>
          </div>
        </div>

        <!-- Payment gateway instructions by type -->
        <?php if ($payment['gateway'] === 'mpesa'): ?>
        <div class="bg-green-50 border border-green-200 rounded-xl p-4 mb-6">
          <div class="flex items-center gap-2 mb-2">
            <i data-lucide="smartphone" class="w-5 h-5 text-green-600"></i>
            <p class="font-semibold text-green-800 text-sm">M-Pesa STK Push</p>
          </div>
          <p class="text-green-700 text-sm">An STK push has been sent to your registered phone number. Please check your phone and enter your M-Pesa PIN to complete the payment.</p>
          <p class="text-green-600 text-xs mt-2 font-medium">⏱ Request expires in 2 minutes</p>
        </div>
        <?php elseif ($payment['gateway'] === 'stripe'): ?>
        <div class="bg-blue-50 border border-blue-200 rounded-xl p-4 mb-6">
          <p class="text-blue-800 text-sm font-medium mb-1">Card Payment via Stripe</p>
          <p class="text-blue-700 text-sm">You will be redirected to Stripe's secure payment page to complete your card payment.</p>
        </div>
        <?php elseif ($payment['gateway'] === 'paypal'): ?>
        <div class="bg-sky-50 border border-sky-200 rounded-xl p-4 mb-6">
          <p class="text-sky-800 text-sm font-medium mb-1">PayPal Payment</p>
          <p class="text-sky-700 text-sm">You will be redirected to PayPal to complete your payment securely.</p>
        </div>
        <?php else: ?>
        <div class="bg-indigo-50 border border-indigo-200 rounded-xl p-4 mb-6">
          <p class="text-indigo-800 text-sm font-medium mb-1">Paystack Payment</p>
          <p class="text-indigo-700 text-sm">Complete your payment via Paystack — card, bank transfer or mobile money.</p>
        </div>
        <?php endif; ?>

        <!-- DEV MODE: Simulate success/fail since live keys not added yet -->
        <div class="bg-amber-50 border border-amber-200 rounded-xl p-3 mb-4 text-center">
          <p class="text-amber-700 text-xs font-medium mb-1">🔧 Development Mode — Payment Gateway Not Live Yet</p>
          <p class="text-amber-600 text-xs">Live API keys will be added later. Use the buttons below to simulate a payment result.</p>
        </div>

        <form method="POST" class="space-y-3">
          <button type="submit" name="result" value="success"
            class="w-full bg-green-600 text-white py-3.5 rounded-xl font-bold text-sm hover:bg-green-700 transition-colors flex items-center justify-center gap-2">
            <i data-lucide="check-circle" class="w-5 h-5"></i>
            Simulate Successful Payment
          </button>
          <button type="submit" name="result" value="fail"
            class="w-full bg-red-100 text-red-700 py-3.5 rounded-xl font-bold text-sm hover:bg-red-200 transition-colors flex items-center justify-center gap-2">
            <i data-lucide="x-circle" class="w-5 h-5"></i>
            Simulate Failed Payment
          </button>
        </form>

        <p class="text-center text-xs text-gray-400 mt-4">
          Reference: <?= htmlspecialchars($payment['receipt_id']) ?>
        </p>
      </div>
    </div>
  </div>
</div>
<?php require_once $_SERVER['DOCUMENT_ROOT'] . '/ByteClass/includes/scripts.php'; ?>
