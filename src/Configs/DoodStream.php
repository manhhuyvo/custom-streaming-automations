<?php

namespace StreamingAutomations\Configs;

use StreamingAutomations\Configs\ConfigInterface;

class DoodStream implements ConfigInterface
{
    public static function all(): array
    {
        return [
            'base_url' => env('DOODSTREAM_BASE_URL'),
            'api_key' => env('DOODSTREAM_API_KEY'),
        ];
    }

    public static function getSimpleName(): string
    {
        return 'doodstream';
    }
}