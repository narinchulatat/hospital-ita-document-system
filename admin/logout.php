<?php
session_start();
require_once '../config/config.php';
require_once '../classes/ActivityLog.php';

// Log the logout activity
if (isset($_SESSION['user_id'])) {
    try {
        $activityLog = new ActivityLog();
        $activityLog->log($_SESSION['user_id'], ACTION_LOGOUT, null, null, null, $_SERVER['REMOTE_ADDR'], $_SERVER['HTTP_USER_AGENT']);
    } catch (Exception $e) {
        error_log("Logout activity log error: " . $e->getMessage());
    }
}

// Clear all session data
session_unset();
session_destroy();

// Redirect to login page
header('Location: ' . BASE_URL . '/admin/login.php');
exit;
?>