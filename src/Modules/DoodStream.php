<?php 

namespace StreamingAutomations\Modules;

use Exception;
use StreamingAutomations\Modules\Client\DoodStreamClient;
use GuzzleHttp\Psr7\Response;
use GuzzleHttp\Psr7\Stream;
use StreamingAutomations\Modules\ModuleResponse;

class DoodStream
{
    public const UPLOAD_TYPE_MP4 = '.mp4';
    public const UPLOAD_TYPE_WMV = '.wmv';
    public const UPLOAD_TYPE_AVI = '.avi';
    public const UPLOAD_TYPE_MOV = '.mov';
    public const UPLOAD_TYPE_MKV = '.mkv';

    public const AVAILABLE_UPLOAD_TYPES = [
        self::UPLOAD_TYPE_MP4,
        self::UPLOAD_TYPE_WMV,
        self::UPLOAD_TYPE_AVI,
        self::UPLOAD_TYPE_MOV,
        self::UPLOAD_TYPE_MKV,
    ];

    private DoodStreamClient $client;

    public function __construct()
    {
        $this->client = new DoodStreamClient();
    }

    /** UPLOAD REQUESTS */
    public function getUploadUrlsList(): ModuleResponse
    {
        return $this->resolveResponse('urluploadList');
    }

    public function getUploadServer(): ModuleResponse
    {
        return $this->resolveResponse('uploadServer');
    }

    public function uploadFileToServer(string $uploadServer, array $data): ModuleResponse
    {
        try {
            /** @var Response $response */
            $response = $this->client->uploadFile($uploadServer, $data);
            if ($response->getStatusCode() != 200) {
                throw new Exception ('Some errors occurred.');
            }

            $responseContent = $response->getBody()->getContents();
            $responseContent = json_decode($responseContent, true);

            if ($responseContent['status'] != 200) {
                $error = $responseContent['msg'] ?? 'Unexpected errors occurred.';

                throw new Exception("Request has failed with error: {$error}");
            }

            return ModuleResponse::success()
                ->message('Successfully uploaded file to server.')
                ->data($responseContent);
        } catch (Exception $e) {
            return ModuleResponse::error()
                ->message($e->getMessage());
        }
    }

    /** FILE REQUESTS */
    public function getFilesList(array $options = []): ModuleResponse
    {
        return $this->resolveResponse('fileList', $options);
    }

    public function getFileInfo(array $options = []): ModuleResponse
    {
        return $this->resolveResponse('fileInfo', $options);
    }

    public function moveFileToFolder(array $options = []): ModuleResponse
    {
        return $this->resolveResponse('fileMove', $options);
    }

    /**
    * ACCOUNT REQUESTS
    */
    public function getAccountInfo()
    {
        return $this->resolveResponse('accountInfo');
    }

    /** FOLDER REQUESTS */
    public function getFoldersList(array $options = []): ModuleResponse
    {
        return $this->resolveResponse('folderList', $options);
    }

    public function createFolder(array $options = []): ModuleResponse
    {
        return $this->resolveResponse('folderCreate', $options);
    }

    public function renameFolder(array $options = []): ModuleResponse
    {
        return $this->resolveResponse('folderRename', $options);
    }

    private function resolveResponse(string $function, array $options = []): ModuleResponse
    {
        try {
            /** @var Response $response */
            $response = $this->client->{$function}($options);
            if ($response->getStatusCode() != 200) {
                throw new Exception ('Some errors occurred.');
            }

            $responseContent = $response->getBody()->getContents();
            $responseContent = json_decode($responseContent, true);

            if ($responseContent['status'] != 200) {
                $error = $responseContent['msg'] ?? 'Unexpected errors occurred.';

                throw new Exception("Request has failed with error: {$error}");
            }

            return ModuleResponse::success()
                ->message('Successfully processed request endpoint.')
                ->data($responseContent);
        } catch (Exception $e) {
            return ModuleResponse::error()
                ->message($e->getMessage());
        }
    }
}