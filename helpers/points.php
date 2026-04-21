<?php
require_once __DIR__ . '/../config/database.php';

function award_points(int $user_id, int $points, string $reason): void {
    try {
        $db = Database::getInstance()->getConnection();
        $db->prepare("UPDATE users SET points = points + ? WHERE id = ? AND role = 'student'")
           ->execute([$points, $user_id]);
        $db->prepare("INSERT INTO activity_logs (user_id, action, description, ip_address) VALUES (?,?,?,?)")
           ->execute([$user_id, 'points_awarded', "Awarded $points points: $reason", $_SERVER['REMOTE_ADDR'] ?? 'system']);
    } catch (Exception $e) {
        error_log("Points error: " . $e->getMessage());
    }
}

// Points rules:
// Login:              +50  (once per day)
// Platform activity:  +50  (various actions)
// Lesson complete:    +100
// Quiz pass:          +100
// Module complete:    +500
// Course enroll:      +500
// Course complete:    +1000 (from certificates)
// First login ever:   +1000

function award_daily_login(int $user_id): void {
    try {
        $db = Database::getInstance()->getConnection();
        // Only once per day
        $check = $db->prepare("SELECT created_at FROM activity_logs WHERE user_id=? AND action='daily_login_points' AND DATE(created_at)=CURDATE() LIMIT 1");
        $check->execute([$user_id]);
        if (!$check->fetch()) {
            award_points($user_id, 50, 'Daily login bonus');
            $db->prepare("INSERT INTO activity_logs (user_id,action,description,ip_address) VALUES (?,?,?,?)")
               ->execute([$user_id,'daily_login_points','Daily login points awarded',$_SERVER['REMOTE_ADDR'] ?? '']);
        }
    } catch (Exception $e) {}
}
