<?php
$envPath = __DIR__ . '/../.env';
if (file_exists($envPath)) {
    $env = parse_ini_file($envPath, false, INI_SCANNER_TYPED);
} else {
    $env = [];
}

define('DB_HOST', $env['DB_HOST'] ?? 'localhost');
define('DB_NAME', $env['DB_NAME'] ?? 'studyshare');
define('DB_USER', $env['DB_USER'] ?? 'root');
define('DB_PASS', $env['DB_PASS'] ?? '');
define('DB_CHARSET', $env['DB_CHARSET'] ?? 'utf8mb4');
define('GEMINI_API_KEY', $env['GEMINI_API_KEY'] ?? '');

define('BASE_URL', $env['BASE_URL'] ?? '/studyshare');
define('ASSETS_URL', BASE_URL . '/assets');
define('UPLOAD_URL', BASE_URL . '/uploads');
define('PROFILE_URL', UPLOAD_URL . '/profile');

define('UPLOAD_DIR', 'D:/StudyShareStorage/uploads/');
define('SCANNED_DIR', 'D:/StudyShareStorage/scanned/');
define('TEMP_DIR', 'D:/StudyShareStorage/temp/');
define('PROFILE_DIR', UPLOAD_DIR . 'profile/');
define('NOTES_DIR', UPLOAD_DIR . 'notes/');
define('MAX_FILE_SIZE', 10 * 1024 * 1024); // 10MB
define('ALLOWED_EXTENSIONS', ['pdf', 'doc', 'docx']);
define('ALLOWED_MIME_TYPES', [
    'application/pdf',
    'application/msword',
    'application/vnd.openxmlformats-officedocument.wordprocessingml.document',
    'image/jpeg',
    'image/png',
    'image/jpg'
]);

// Google Drive Configuration
define('DRIVE_CLIENT_SECRET_JSON', __DIR__ . '/client_secret.json');
define('DRIVE_TOKEN_JSON', __DIR__ . '/token.json');
define('DRIVE_FOLDER_ID', $env['DRIVE_FOLDER_ID'] ?? '1yJ3sCZ95CrCrfM_CCfRg1Lk1BAkFF76e');

define('DRIVE_FOLDER_NORMAL', '1-31ExYIoMe33KX3RzBTaF5msKFq5yo3P');
define('DRIVE_FOLDER_SCANNED', '11SPgo7ra1YC-rakrQAXp_dEOGRFcCfsU');
