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

    public function getAccountInformation(): ModuleResponse
    {
        try {
            /** @var Response $response */
            $response = $this->client->accountInformation();
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

    public function getAccountStats()
    {
        try {
            /** @var Response $response */
            $response = $this->client->accountStats();
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
                ->message('Successfully retrieved account stats.')
                ->data($responseContent);
        } catch (Exception $e) {
            return ModuleResponse::error()
                ->message($e->getMessage());
        }
    }
}