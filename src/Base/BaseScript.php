<?php

namespace StreamingAutomations\Base;

require 'src/custom-autoload.php';

class BaseScript
{
    public function __construct($argv) {
        print_r(collect($argv)->toArray());
    }

    public function handle()
    {
        echo "haha";
    }
}

$script = new BaseScript($argv);

return $script->handle();