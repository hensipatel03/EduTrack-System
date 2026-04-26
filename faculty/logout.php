<?php
// faculty/logout.php
declare(strict_types=1);

session_start();
$_SESSION = [];
session_destroy();
require_once __DIR__ . '/../config.php';

header('Location: ' . BASE_URL . '/faculty/login.php');
exit;