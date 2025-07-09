<?php
require_once 'includes/header.php';

// Logout user
logoutUser();

// Redirect to login page
redirectTo(BASE_URL . '/login.php');
?>