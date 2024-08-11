<?php

namespace StreamingAutomations\Scripts;
require_once '..\custom-autoload.php';

use StreamingAutomations\Base\BaseScript;
use StreamingAutomations\Modules\LuluStream as LuluStreamModule;

class UploadVideos extends BaseScript
{
    public const TYPE_LULU = 'lulustream';

    public const TYPE_DOOD = 'doodstream';

    public function handle(): int
    {
        $total = [
            [
                'title' => "Title Number One",
                'video' => 'video_one.mp4',
            ],
            [
                'title' => "Title Number Two",
                'video' => 'video_two.mp4',
            ],
        ];

        $this->success("Checking file videos.csv for uploading...");

        $this->success("Found total " . count($total) . 'videos for processing...');



        $this->setIteration(count($total));
        foreach ($total as $video) {
            $this->increaseIteration();

            $prefix = "[{$video['title']}] ({$video['video']})";
            $this->iteration("Uploading video {$prefix}...", self::TYPE_WARNING);
            $this->iteration("Successfully uploaded video {$prefix}...");
            $this->line();
        }

        return 0;
    }
}

$script = new UploadVideos($argv);

return $script->handle();