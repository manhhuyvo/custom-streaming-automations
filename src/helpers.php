<?php
require_once 'vendor/autoload.php';

use StreamingAutomations\Configs\BaseConfigs;

if (! function_exists('config')) {
    function config(string $key = '', $default = null)
    {
        $configs = new BaseConfigs();

        return $configs->get($key, $default);
    }
}

if (! function_exists('isFolderExisted')) {
    function isFolderExisted(string $path = '')
    {
        $path = realpath($path);

        if (!$path || !is_dir($path)) {
            return false;
        }

        return true;
    }
}