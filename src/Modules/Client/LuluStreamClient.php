<?php

namespace StreamingAutomations\Modules\Client;

use GuzzleHttp\Client;
use Exception;

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

    public function accountInformation()
    {
        return  $this->get('account/info');
    }

    public function accountStats()
    {
        return $this->get('account/stats');
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
}