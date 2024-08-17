<?php

namespace StreamingAutomations\Modules\Client;

use GuzzleHttp\Client;
use Exception;
use GuzzleHttp\Psr7\Request;
use GuzzleHttp\Psr7\Utils;

class LuluStreamClient
{    
    private string $baseUrl = '';
    private string $apiKey = '';

    public const METHOD_POST = 'POST';

    public const METHOD_GET = 'GET';
    
    public function __construct()
    {
        $this->baseUrl = config('lulustream.base_url', '');
        $this->apiKey = config('lulustream.api_key', '');

        if (empty($this->baseUrl) || empty($this->apiKey)) {
            throw new Exception('Lulusstream Base URL or API Key is missing.');
        }
    }

    /**
     * UPLOAD REQUESTS
     */
    public function uploadServer()
    {
        return $this->get('upload/server');
    }

    public function uploadFile(string $uploadServer, array $data)
    {
        $data = collect($data)
            ->only([
                'file',
                'file_title',
                'file_descr',
                'snapshot',
                'fld_id',
                'cat_id',
                'tags',
                'file_public',
                'file_adult',
                'html_redirect',
            ])
            ->put('key', $this->apiKey)
            ->map(function ($value, $key) {
                if ($key == 'file') {
                    return [
                        'name' => $key,
                        'contents' => Utils::tryFopen($value, 'r'),
                    ];
                }

                return [
                    'name' => $key,
                    'contents' => $value,
                ];
            })
            ->all();

        $client = $this->getClient($uploadServer);

        return $client->request(self::METHOD_POST, '', [
            'multipart' => $data,
        ]);
    }

    /**
     * FOLDER REQUESTS
     */

    public function folderList(array $options = [])
    {
        return $this->get('folder/list', $options);
    }

    public function folderCreate(array $options = [])
    {
        return $this->get('folder/create', $options);
    }

    public function folderEdit(array $options = [])
    {
        return $this->get('folder/edit', $options);
    }

    /**
     * ACCOUNT REQUESTS
     */
    public function accountInformation()
    {
        return  $this->get('account/info');
    }

    public function accountStats()
    {
        return $this->get('account/stats');
    }

    /**
     * FILE REQUESTS
     */
    public function fileList(array $options = [])
    {
        return $this->get('file/list', $options);
    }

    private function getClient(string $baseUri, array $options = []): Client
    {
        if (! empty($options)) {
            $options = collect($options)
                ->except(['base_uri'])
                ->filter()
                ->toArray();
        }

        $configs = array_merge($options, [
            'base_uri' => $baseUri,
        ]);

        return new Client($configs);
    }

    private function get(string $endpoint, array $data = [])
    {
        if (! empty($data)) {
            $data = collect($data)
                ->except(['key'])
                ->filter()
                ->toArray();
        }

        $urlParams = array_merge($data, [
            'key' => $this->apiKey,
        ]);

        $urlParams = collect($urlParams)
            ->map(function ($value, $key) {
                $value = urlencode($value);
                $key = urldecode($key);

                return "{$key}={$value}";
            })
            ->implode('&');

        return $this->getClient($this->baseUrl)->get("api/{$endpoint}?{$urlParams}");
    }

    private function post(string $url, array $data)
    {

        return $this->getClient($url)->post($url, $data);
    }
}