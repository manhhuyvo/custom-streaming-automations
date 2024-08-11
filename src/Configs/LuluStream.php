<?php

namespace StreamingAutomations\Configs;

use StreamingAutomations\Configs\ConfigInterface;

class LuluStream implements ConfigInterface
{
    public static function all(): array
    {
        return [
            'base_url' => env('LULUSTREAM_BASE_URL'),
            'api_key' => env('LULUSTREAM_API_KEY'),
        ];
    }

    public static function getSimpleName(): string
    {
        return 'lulustream';
    }
}