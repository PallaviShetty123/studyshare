<?php
require_once __DIR__ . '/db.php';
require_once __DIR__ . '/functions.php';

/**
 * Check if a lecturer is logged in
 */
function isLecturerLoggedIn() {
    return isset($_SESSION['lecturer_id']) && !empty($_SESSION['lecturer_id']);
}

/**
 * Get current logged-in lecturer
 */
function getCurrentLecturer() {
    if (!isLecturerLoggedIn()) {
        redirect('login.php');
    }
    
    $pdo = db();
    $stmt = $pdo->prepare('SELECT * FROM lecturers WHERE id = ?');
    $stmt->execute([$_SESSION['lecturer_id']]);
    $lecturer = $stmt->fetch();
    
    if (!$lecturer) {
        session_destroy();
        redirect('login.php');
    }
    
    return $lecturer;
}

/**
 * Set lecturer session
 */
function setLecturerSession($lecturer) {
    $_SESSION['lecturer_id'] = $lecturer['id'];
    $_SESSION['lecturer_name'] = $lecturer['name'];
    $_SESSION['lecturer_username'] = $lecturer['username'];
}

/**
 * Logout lecturer
 */
function lecturerLogout() {
    session_destroy();
    redirect('login.php');
}
?>
