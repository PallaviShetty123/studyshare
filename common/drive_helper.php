<?php
require_once __DIR__ . '/../vendor/autoload.php';
require_once __DIR__ . '/config.php';

class DriveHelper {
    private $client;
    private $service;

    public function __construct() {
        $this->client = new Google\Client();
        $this->client->setAuthConfig(DRIVE_CLIENT_SECRET_JSON);
        $this->client->addScope(Google\Service\Drive::DRIVE_FILE);
        $this->client->setAccessType('offline');

        if (file_exists(DRIVE_TOKEN_JSON)) {
            $accessToken = json_decode(file_get_contents(DRIVE_TOKEN_JSON), true);
            $this->client->setAccessToken($accessToken);

            // If the token is expired, refresh it.
            if ($this->client->isAccessTokenExpired()) {
                if ($this->client->getRefreshToken()) {
                    $this->client->fetchAccessTokenWithRefreshToken($this->client->getRefreshToken());
                    file_put_contents(DRIVE_TOKEN_JSON, json_encode($this->client->getAccessToken()));
                }
            }
        } else {
            // No token found. We should inform the user to visit oauth2callback.php
            throw new Exception("Google Drive not authorized. Please visit " . BASE_URL . "/common/oauth2callback.php to authorize.");
        }
        
        $this->service = new Google\Service\Drive($this->client);
    }

    /**
     * Upload a file to Google Drive
     * @param string $filePath Local path to the file
     * @param string $fileName Name to give the file in Drive
     * @param string $mimeType MIME type of the file
     * @return string Google Drive File ID
     */
    public function uploadFile($filePath, $fileName, $mimeType = 'application/pdf') {
        $fileMetadata = new Google\Service\Drive\DriveFile([
            'name' => $fileName
        ]);

        if (defined('DRIVE_FOLDER_ID') && !empty(DRIVE_FOLDER_ID)) {
            $fileMetadata->setParents([DRIVE_FOLDER_ID]);
        }

        $content = file_get_contents($filePath);

        $file = $this->service->files->create($fileMetadata, [
            'data' => $content,
            'mimeType' => $mimeType,
            'uploadType' => 'multipart',
            'fields' => 'id',
            'supportsAllDrives' => true
        ]);

        return $file->id;
    }

    /**
     * Get a web view link for a file
     * @param string $fileId Google Drive File ID
     * @return string Link to view the file
     */
    public function getViewLink($fileId) {
        $file = $this->service->files->get($fileId, ['fields' => 'webViewLink']);
        return $file->webViewLink;
    }

    /**
     * Delete a file from Google Drive
     * @param string $fileId Google Drive File ID
     */
    public function deleteFile($fileId) {
        try {
            $this->service->files->delete($fileId);
            return true;
        } catch (Exception $e) {
            return false;
        }
    }

    /**
     * Share a file so anyone with the link can view it
     * @param string $fileId Google Drive File ID
     */
    public function makePublic($fileId) {
        $userPermission = new Google\Service\Drive\Permission([
            'type' => 'anyone',
            'role' => 'reader',
        ]);
        $this->service->permissions->create($fileId, $userPermission);
    }
}
