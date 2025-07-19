<?php
require_once 'config.php';

session_destroy();
redirect('login.php?logout=1');
?>

