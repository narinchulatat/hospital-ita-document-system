<?php
require_once 'includes/auth.php';

// Redirect based on user role or to public portal
if (isLoggedIn()) {
    redirectTo(getRoleRedirectUrl(getCurrentUserRole()));
} else {
    redirectTo('/public/');
}
?>