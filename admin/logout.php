<?php
require_once __DIR__ . '/../common/functions.php';

// Destroy session
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}
$_SESSION = [];
session_destroy();

// Redirect to admin login
redirect('login.php');
