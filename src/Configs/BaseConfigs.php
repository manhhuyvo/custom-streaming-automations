<?php

namespace StreamingAutomations\Configs;

use Illuminate\Support\Arr;
use StreamingAutomations\Configs\LuluStream;
use StreamingAutomations\Configs\DoodStream;
use StreamingAutomations\Configs\ConfigInterface;

class BaseConfigs
{
    public const CONFIGS = [
        LuluStream::class,
        DoodStream::class,
    ];

    private array $data = [];
    
    public function __construct()
    {
        $data = [];
        foreach (self::CONFIGS as $configClass) {
            /** @var ConfigInterface $configClass */
            $class = new $configClass;

            $data[$class::getSimpleName()] = $class::all();
        }

        $this->data = Arr::dot($data);
    }

    public function get(string $key, $default = null)
    {
        return $this->data[$key] ?? $default;
    }
}