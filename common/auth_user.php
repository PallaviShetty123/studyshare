<?php
require_once __DIR__ . '/functions.php';

if (!isStudentLoggedIn()) {
    redirect('../user/login.php');
}
