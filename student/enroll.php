<?php
$required_role = 'student';
require_once $_SERVER['DOCUMENT_ROOT'] . '/ByteClass/includes/auth-check.php';
require_once $_SERVER['DOCUMENT_ROOT'] . '/ByteClass/config/constants.php';
require_once $_SERVER['DOCUMENT_ROOT'] . '/ByteClass/config/database.php';
require_once $_SERVER['DOCUMENT_ROOT'] . '/ByteClass/helpers/response.php';
require_once $_SERVER['DOCUMENT_ROOT'] . '/ByteClass/helpers/points.php';

$db        = Database::getInstance()->getConnection();
$id        = (int)$_SESSION['user_id'];
$course_id = (int)($_GET['course_id'] ?? 0);

if (!$course_id) {
    header('Location: ' . APP_URL . '/student/explore.php'); exit;
}

// Get course
$stmt = $db->prepare("SELECT * FROM courses WHERE id=? AND status='published' LIMIT 1");
$stmt->execute([$course_id]);
$course = $stmt->fetch();

if (!$course) {
    header('Location: ' . APP_URL . '/student/explore.php'); exit;
}

// Already enrolled?
$check = $db->prepare("SELECT id FROM enrollments WHERE student_id=? AND course_id=? LIMIT 1");
$check->execute([$id, $course_id]);
if ($check->fetch()) {
    header('Location: ' . APP_URL . '/student/courses.php'); exit;
}

// Free course — enroll directly
if ($course['price_kes'] == 0 && $course['price_usd'] == 0) {
    $db->prepare("INSERT INTO enrollments (student_id, course_id) VALUES (?,?)")->execute([$id, $course_id]);
    award_points($id, 500, 'Enrolled in: ' . $course['name']);

    // Notify student
    $db->prepare("INSERT INTO notifications (user_id,title,message,type) VALUES (?,?,?,?)")
       ->execute([$id, 'Enrolled Successfully!', 'You have enrolled in: ' . $course['name'], 'course']);

    $db->prepare("INSERT INTO activity_logs (user_id,action,target_type,target_id,description,ip_address) VALUES (?,?,?,?,?,?)")
       ->execute([$id,'enroll','course',$course_id,'Student enrolled in: '.$course['name'],$_SERVER['REMOTE_ADDR']??'']);

    header('Location: ' . APP_URL . '/student/courses.php?enrolled=1'); exit;
}

// Paid course — redirect to payment (to be implemented)
header('Location: ' . APP_URL . '/student/payment.php?course_id=' . $course_id); exit;
