<?php
require_once 'config.php';

if (isLoggedIn() && isAdmin()) {
    redirect('dashboard.php');
} else {
    redirect('login.php');
}
?>

