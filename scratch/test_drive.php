<?php
require_once __DIR__ . '/../common/config.php';
require_once __DIR__ . '/../common/drive_helper.php';

try {
    $drive = new DriveHelper();
    echo "DriveHelper initialized.\n";
    $viewLink = $drive->getViewLink('1Z45vzzolGixtzik0V_0lcQZnNgt4ECbW');
    echo "View Link for ID 5: " . $viewLink . "\n";
} catch (Exception $e) {
    echo "DriveHelper Exception: " . $e->getMessage() . "\n";
}
