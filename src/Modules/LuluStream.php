<?php

namespace StreamingAutomations\Modules;

use Exception;
use StreamingAutomations\Modules\Client\LuluStreamClient;
use GuzzleHttp\Psr7\Response;
use GuzzleHttp\Psr7\Stream;
use StreamingAutomations\Modules\ModuleResponse;

class LuluStream
{
    private LuluStreamClient $client;

    public function __construct()
    {
        $this->client = new LuluStreamClient();
    }

    /** UPLOAD REQUESTS */
    public function getUploadServer(): ModuleResponse
    {
        return $this->resolveResponse('uploadServer');
    }

    /** FILE REQUESTS */
    public function getFilesList(array $options = []): ModuleResponse
    {
        return $this->resolveResponse('fileList', $options);
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

    public function editFolder(array $options = []): ModuleResponse
    {
        return $this->resolveResponse('folderEdit', $options);
    }

    /** ACCOUNT REQUESTS */
    public function getAccountInformation(): ModuleResponse
    {
        return $this->resolveResponse('accountInformation');
    }

    public function getAccountStats(): ModuleResponse
    {
        return $this->resolveResponse('accountStats');
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
                ->message('Successfully retrieved account information.')
                ->data($responseContent);
        } catch (Exception $e) {
            return ModuleResponse::error()
                ->message($e->getMessage());
        }
    }
}