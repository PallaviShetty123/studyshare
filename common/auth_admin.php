<?php
require_once __DIR__ . '/functions.php';

if (!isAdminLoggedIn()) {
    redirect('../admin/login.php');
}
