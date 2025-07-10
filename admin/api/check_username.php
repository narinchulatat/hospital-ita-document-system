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

$username = trim($_POST['username'] ?? '');

if (empty($username)) {
    echo json_encode(['exists' => false]);
    exit;
}

try {
    $user = new User();
    $exists = $user->usernameExists($username);
    
    echo json_encode(['exists' => $exists]);
    
} catch (Exception $e) {
    error_log("Check username error: " . $e->getMessage());
    http_response_code(500);
    echo json_encode(['error' => 'Server error']);
}
?>