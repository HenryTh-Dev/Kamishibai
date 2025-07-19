<?php
require_once 'config.php';

if (isLoggedIn() && isAdmin()) {
    redirect("painel-uti.php");
} else {
    redirect('login.php');
}
?>

