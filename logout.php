<?php
require_once __DIR__ . '/common/functions.php';

// Destroy session
session_destroy();

// Redirect to home
redirect('user/login.php');
