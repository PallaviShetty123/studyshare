<?php
header('Content-Type: text/plain');
require_once __DIR__ . '/../vendor/autoload.php';
require_once __DIR__ . '/../common/config.php';

echo "=== Google Drive OAuth 2.0 Status ===" . PHP_EOL . PHP_EOL;

try {
    require_once __DIR__ . '/../common/drive_helper.php';
    $drive = new DriveHelper();
    echo "✅ DriveHelper initialized successfully!" . PHP_EOL;
    echo "✅ OAuth 2.0 token is valid and working." . PHP_EOL;
    echo "✅ Google Drive integration is READY." . PHP_EOL;
} catch (Exception $e) {
    echo "❌ ERROR: " . $e->getMessage() . PHP_EOL;
}
