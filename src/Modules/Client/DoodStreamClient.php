<?php

namespace StreamingAutomations\Modules\Client;

use GuzzleHttp\Client;
use Exception;
use GuzzleHttp\Psr7\Request;
use GuzzleHttp\Psr7\Utils;

class DoodStreamClient
{
    private string $baseUrl = '';
    private string $apiKey = '';

    public const METHOD_POST = 'POST';

    public const METHOD_GET = 'GET';
    
    public function __construct()
    {
        $this->baseUrl = config('doodstream.base_url', '');
        $this->apiKey = config('doodstream.api_key', '');

        if (empty($this->baseUrl) || empty($this->apiKey)) {
            throw new Exception('DoodStream Base URL or API Key is missing.');
        }
    }

    /**
     * ACCOUNT REQUESTS
     */

    public function accountInfo()
    {
        return $this->get('account/info');
    }

    public function accountReport()
    {
        return $this->get('account/stats');
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