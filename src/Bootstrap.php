<?php
namespace StreamingAutomations;

require_once 'vendor/autoload.php';

use Symfony\Component\Dotenv\Dotenv;

class Bootstrap
{
    public static function instantiate()
    {        
        // Unbelievable that we have to load this one manually
        $dotenv = new Dotenv();
        $dotenv->loadEnv('.env');

        // All the packages used in Illuminate Support Env not installed
        // Remember to install it manually
    }
}