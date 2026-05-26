<?php
require_once __DIR__ . '/../vendor/autoload.php';
require_once __DIR__ . '/config.php';

$client = new Google\Client();
$client->setAuthConfig(DRIVE_CLIENT_SECRET_JSON);
$client->setAccessType('offline');
$client->setPrompt('select_account consent');
$client->addScope(Google\Service\Drive::DRIVE_FILE);
$client->setRedirectUri('http://localhost/studyshare/common/oauth2callback.php');

if (!isset($_GET['code'])) {
    $authUrl = $client->createAuthUrl();
    header('Location: ' . filter_var($authUrl, FILTER_SANITIZE_URL));
    exit;
} else {
    $accessToken = $client->fetchAccessTokenWithAuthCode($_GET['code']);
    if (isset($accessToken['error'])) {
        die("Fatal Error: " . $accessToken['error_description']);
    }
    
    // Save the token to a file.
    if (!file_exists(dirname(DRIVE_TOKEN_JSON))) {
        mkdir(dirname(DRIVE_TOKEN_JSON), 0700, true);
    }
    file_put_contents(DRIVE_TOKEN_JSON, json_encode($client->getAccessToken()));
    
    echo "<h1>Authorization Successful!</h1>";
    echo "<p>The token has been saved. You can now close this window and try uploading files.</p>";
    echo "<a href='../lecture/dashboard.php'>Go to Dashboard</a>";
}
