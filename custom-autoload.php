<?php
require_once 'vendor/autoload.php';

use Symfony\Component\Dotenv\Dotenv;

// Unbelievable that we have to load this one manually
$dotenv = new Dotenv();
$dotenv->loadEnv(__DIR__ . '\.env');

// All the packages used in Illuminate Support Env not installed
// Remember to install it manually