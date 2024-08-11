<?php
require_once '../vendor/autoload.php';

use StreamingAutomations\Configs\BaseConfigs;
use Symfony\Component\Dotenv\Dotenv;

if (! function_exists('config')) {
    function config(string $key = '', $default = null)
    {
        $configs = new BaseConfigs();

        return $configs->get($key, $default);
    }
}