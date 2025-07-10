<?php
header('Content-Type: application/json');
require_once '../../includes/auth.php';
require_once '../../classes/User.php';

requireAdminAuth();

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    http_response_code(405);
    echo json_encode(['error' => 'Method not allowed']);
    exit;
}

$email = trim($_POST['email'] ?? '');

if (empty($email)) {
    echo json_encode(['exists' => false]);
    exit;
}

try {
    $user = new User();
    $exists = $user->emailExists($email);
    
    echo json_encode(['exists' => $exists]);
    
} catch (Exception $e) {
    error_log("Check email error: " . $e->getMessage());
    http_response_code(500);
    echo json_encode(['error' => 'Server error']);
}
?>