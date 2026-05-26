<?php
require_once __DIR__ . '/../vendor/autoload.php';
require_once __DIR__ . '/../common/config.php';

echo "=== Token File Check ===" . PHP_EOL;
$tokenPath = DRIVE_TOKEN_JSON;
echo "Token path: " . $tokenPath . PHP_EOL;
echo "Token exists: " . (file_exists($tokenPath) ? 'YES' : 'NO') . PHP_EOL;

if (file_exists($tokenPath)) {
    $token = json_decode(file_get_contents($tokenPath), true);
    echo "Access token: " . substr($token['access_token'] ?? 'MISSING', 0, 30) . "..." . PHP_EOL;
    echo "Refresh token: " . (isset($token['refresh_token']) ? 'PRESENT' : 'MISSING') . PHP_EOL;
    echo "Created: " . ($token['created'] ?? 'MISSING') . PHP_EOL;
    echo "Expires in: " . ($token['expires_in'] ?? 'MISSING') . " seconds" . PHP_EOL;
    
    $expiresAt = ($token['created'] ?? 0) + ($token['expires_in'] ?? 0);
    echo "Expired: " . ($expiresAt < time() ? 'YES (expired ' . (time() - $expiresAt) . 's ago)' : 'NO') . PHP_EOL;
    
    if (isset($token['refresh_token_expires_in'])) {
        $refreshExpiresAt = ($token['created'] ?? 0) + $token['refresh_token_expires_in'];
        echo "Refresh token expired: " . ($refreshExpiresAt < time() ? 'YES' : 'NO (expires in ' . ($refreshExpiresAt - time()) . 's)') . PHP_EOL;
    }
}

echo PHP_EOL . "=== Attempting DriveHelper ===" . PHP_EOL;
try {
    require_once __DIR__ . '/../common/drive_helper.php';
    $drive = new DriveHelper();
    echo "DriveHelper created successfully!" . PHP_EOL;
    echo "Google Drive OAuth2 is WORKING!" . PHP_EOL;
} catch (Exception $e) {
    echo "ERROR: " . $e->getMessage() . PHP_EOL;
    echo "File: " . $e->getFile() . ":" . $e->getLine() . PHP_EOL;
}
