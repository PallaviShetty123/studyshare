<?php
require_once __DIR__ . '/config.php';
require_once __DIR__ . '/db.php';

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// Sanitize and return clean data
function sanitize($value)
{
    return htmlspecialchars(trim($value), ENT_QUOTES, 'UTF-8');
}

// Redirect to a page
function redirect($path)
{
    header('Location: ' . $path);
    exit;
}

// Flash messages
function flash($key, $message = null)
{
    if ($message === null) {
        if (!empty($_SESSION['flash'][$key])) {
            $value = $_SESSION['flash'][$key];
            unset($_SESSION['flash'][$key]);
            return $value;
        }
        return null;
    }
    $_SESSION['flash'][$key] = $message;
}

// Check if student is logged in
function isStudentLoggedIn()
{
    return !empty($_SESSION['student_roll_no']);
}

// Check if admin is logged in
function isAdminLoggedIn()
{
    return !empty($_SESSION['admin_id']);
}

// Get current student
function getCurrentStudent()
{
    if (!isStudentLoggedIn()) {
        return null;
    }
    
    $pdo = db();
    $stmt = $pdo->prepare('SELECT * FROM students WHERE roll_no = ?');
    $stmt->execute([$_SESSION['student_roll_no']]);
    return $stmt->fetch();
}

// Get current admin
function getCurrentAdmin()
{
    if (!isAdminLoggedIn()) {
        return null;
    }
    return [
        'id' => $_SESSION['admin_id'],
        'username' => $_SESSION['admin_username']
    ];
}

// Set student session
function setStudentSession($student)
{
    $_SESSION['student_roll_no'] = $student['roll_no'];
    $_SESSION['student_name'] = $student['name'];
    $_SESSION['student_dob'] = $student['dob'];
    $_SESSION['student_department'] = $student['department'];
    $_SESSION['student_semester'] = $student['semester'];
    $_SESSION['student_profile_image'] = $student['profile_image'];
}

// Set admin session
function setAdminSession($admin)
{
    $_SESSION['admin_id'] = $admin['id'];
    $_SESSION['admin_username'] = $admin['username'];
}

// Get profile image path
function getProfileImage($profile_image)
{
    if (!empty($profile_image) && file_exists(PROFILE_DIR . $profile_image)) {
        return PROFILE_URL . '/' . $profile_image;
    }
    return ASSETS_URL . '/images/default-avatar.png';
}

// Check if file upload is valid
function isValidFileUpload($file_name)
{
    $allowed_extensions = ['pdf', 'doc', 'docx'];
    $file_ext = strtolower(pathinfo($file_name, PATHINFO_EXTENSION));
    return in_array($file_ext, $allowed_extensions);
}

// Upload file and return path
function uploadFile($file, $upload_dir)
{
    if (!file_exists($upload_dir)) {
        mkdir($upload_dir, 0755, true);
    }
    
    $filename = uniqid() . '_' . basename($file['name']);
    $filepath = $upload_dir . $filename;
    
    if (move_uploaded_file($file['tmp_name'], $filepath)) {
        return $filename;
    }
    return false;
}

/**
 * Log user activity
 */
function logActivity($user_id, $user_type, $action, $target_id = null, $details = '')
{
    try {
        $pdo = db();
        $stmt = $pdo->prepare('INSERT INTO activity_log (user_id, user_type, action, target_id, details) VALUES (?, ?, ?, ?, ?)');
        $stmt->execute([$user_id, $user_type, $action, $target_id, $details]);
    } catch (Exception $e) {
        // Silently fail to not interrupt main flow
        error_log("Activity Log Error: " . $e->getMessage());
    }
}
