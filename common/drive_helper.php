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

        $authUrl = BASE_URL . '/common/oauth2callback.php';

        if (file_exists(DRIVE_TOKEN_JSON)) {
            $tokenData = json_decode(file_get_contents(DRIVE_TOKEN_JSON), true);
            if (empty($tokenData)) {
                @unlink(DRIVE_TOKEN_JSON);
                throw new Exception("Google Drive authorization not set up properly. Please <a href='{$authUrl}' target='_blank' style='text-decoration: underline; font-weight: bold; color: #dc2626;'>click here to authorize Google Drive</a>.");
            }
            
            $this->client->setAccessToken($tokenData);

            // If the token is expired, refresh it.
            if ($this->client->isAccessTokenExpired()) {
                $refreshToken = $this->client->getRefreshToken();
                if ($refreshToken) {
                    try {
                        $this->client->fetchAccessTokenWithRefreshToken($refreshToken);
                        file_put_contents(DRIVE_TOKEN_JSON, json_encode($this->client->getAccessToken()));
                    } catch (Exception $e) {
                        // Refresh token is invalid/expired (e.g., invalid_grant).
                        // Delete the invalid token.json so that we prompt for a new authorization!
                        @unlink(DRIVE_TOKEN_JSON);
                        throw new Exception("Google Drive authorization has expired or was revoked. Please <a href='{$authUrl}' target='_blank' style='text-decoration: underline; font-weight: bold; color: #dc2626;'>click here to re-authorize Google Drive</a>.");
                    }
                } else {
                    @unlink(DRIVE_TOKEN_JSON);
                    throw new Exception("Google Drive authorization has expired (no refresh token found). Please <a href='{$authUrl}' target='_blank' style='text-decoration: underline; font-weight: bold; color: #dc2626;'>click here to authorize Google Drive</a>.");
                }
            }
        } else {
            throw new Exception("Google Drive not authorized. Please <a href='{$authUrl}' target='_blank' style='text-decoration: underline; font-weight: bold; color: #dc2626;'>click here to authorize Google Drive</a> to enable note uploads.");
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
    public function uploadFile($filePath, $fileName, $mimeType = 'application/pdf', $folderId = null) {
        $fileMetadata = new Google\Service\Drive\DriveFile([
            'name' => $fileName
        ]);

        if ($folderId !== null) {
            $fileMetadata->setParents([$folderId]);
        } elseif (defined('DRIVE_FOLDER_ID') && !empty(DRIVE_FOLDER_ID)) {
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
