<?php
// logout.php
declare(strict_types=1);

session_start();
session_destroy();

// Redirect to home
header('Location: ' . BASE_URL . '/index.php');
exit;