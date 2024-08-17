<?php
namespace StreamingAutomations\Scripts;

require_once 'vendor/autoload.php';

use Exception;
use StreamingAutomations\Base\BaseScript;
use StreamingAutomations\Modules\LuluStream as LuluStreamModule;
use Carbon\Carbon;
use Illuminate\Support\Str;
use League\Csv\Writer;
use StreamingAutomations\Modules\DoodStream;

class MultipleUploadVideos extends BaseScript
{
    public const TYPE_LULU = 'lulustream';

    public const TYPE_DOOD = 'doodstream';
    
    public const AVAILABLE_STREAMS = [
        SELF::TYPE_LULU,
        SELF::TYPE_DOOD,
    ];

    public function handle(): int
    {
        $this->success("Checking available videos for uploading today...");
        
        try {
            $files = $this->prepareFile();
        } catch (Exception $e) {
            $this->error("Failed to execute script with error: {$e->getMessage()}");

            return 0;
        }

        $this->success("Found total " . count($files) . ' videos for processing...');
        
        if (count($files) == 0) {
            $this->success('No videos were added today. No need to proceed...');

            return 1;
        }

        $this->setIteration(count($files));

        if ($this->isDry()) {
            $this->success('The following files will be processed...');

            foreach ($files as $file) {
                $this->increaseIteration();

                $this->iteration("File Location: {$file['location']}", self::TYPE_WARNING);
                $this->iteration("File Thumbnail: {$file['thumbnail']}", self::TYPE_WARNING);
                $this->iteration("File Index (Folder Name): {$file['index']}", self::TYPE_WARNING);
                $this->line();
            }

            return 1;
        }

        $results = [];
        foreach (self::AVAILABLE_STREAMS as $stream) {
            [$success, $failed] = $stream == self::TYPE_LULU
                ? $this->handleLuluStream($files)
                : $this->handleDoodStream($files);

            $results[$stream] = [
                'success' => $success,
                'failed' => $failed,
            ];
            continue;
        }

        $this->success('[SCRIPT ACTION RESULTS]');

        foreach (self::AVAILABLE_STREAMS as $stream) {
            $success = $resuls[$stream]['success'] ?? 0;
            $failed = $resuls[$stream]['failed'] ?? 0;
            $stream = Str::upper($stream);

            $this->success("[{$stream}] [SUCCESS] {$success} RECORD(S)");
            $this->success("[{$stream}] [FAILED] {$failed} RECORD(S)");
        }

        return 0;
    }

    private function prepareFile(): array
    {
        $today = Carbon::today()->format('dmY');

        $dirName = dirname(dirname(__DIR__));

        $todayFolderName = $dirName . "\assets\\{$today}";
        if (! isFolderExisted($todayFolderName)) {
            throw new Exception("Folder name '{$today}' for today does not exist.");
        }

        $todayFolders = collect(scandir($todayFolderName))
            ->filter(function ($folderName) {
                return !empty($folderName) && !Str::contains($folderName, ['.']);
            })
            ->toArray();

        $files = [];
        foreach ($todayFolders as $videoFolderName) {
            $filesInFolder = scandir("{$todayFolderName}\\{$videoFolderName}");

            $videoName = collect($filesInFolder)
                ->filter(function ($file) {
                    return !in_array($file, ['.', '..']) && !empty($file);
                })
                ->filter(function ($name) {
                    return Str::endsWith($name, LuluStreamModule::AVAILABLE_UPLOAD_TYPES);
                })
                ->first();

            $thumbnail = collect($filesInFolder)
                ->filter(function ($file) {
                    return !in_array($file, ['.', '..']) && !empty($file);
                })
                ->reject(function ($name) {
                    return Str::endsWith($name, LuluStreamModule::AVAILABLE_UPLOAD_TYPES) && Str::endsWith($name, ['.txt', '.csv', '.xslx', '.xsl']);
                })
                ->first();
            
            $files["{$todayFolderName}\\{$videoFolderName}\\{$videoName}"] = [
                'location' => "{$todayFolderName}\\{$videoFolderName}\\{$videoName}",
                'title' => Str::replace(LuluStreamModule::AVAILABLE_UPLOAD_TYPES, '', $videoName),
                'thumbnail' => "{$todayFolderName}\\{$videoFolderName}\\{$thumbnail}",
                'index' => $videoFolderName,
            ];
        }

        return $files;
    }

    private function handleDoodStream(array $files): array
    {
        $success = 0;
        $failed = 0;
        $this->warning('STARTING UPLOAD PROCESS FOR DOODSTREAM...');
        $doodStream = new DoodStream();

        // Create a new folder for today
        $newFolderResponse = $doodStream->createFolder([
            'name' => Carbon::now()->format('Y m d H:i:s'),
        ]);

        if (!$newFolderResponse->isSuccesful()) {
            $this->error("[DOODSTREAM] Failed to create folder for today. Error: {$newFolderResponse->getMessage()}...");
            $failed++;

            return [$success, $failed];
        }

        $newFolderId = $newFolderResponse->getData('result.fld_id');

        // Get upload server
        $uploadServerResponse = $doodStream->getUploadServer();

        if (!$uploadServerResponse->isSuccesful()) {
            $this->error("[DOODSTREAM] Failed to get upload server for today. Error: {$uploadServerResponse->getMessage()}...");
            $failed++;

            return [$success, $failed];
        }

        $uploadServer = $uploadServerResponse->getData('result');

        $videoData = collect($files)
            ->map(function ($video) {
                return [
                    'file' => $video['location'],
                    'file_title' => $video['title'],
                    'file_public' => 1,
                    'html_redirect' => 0,
                    'fld_id' => $newFolderId ?? 1,
                ];
            })
            ->toArray();
        
        // Create CSV file
        $csvHeaders = [
            'Folder',
            'Video Code',
            'Title',
            'Thumbnail',
        ];
        $afterUploads = [];

        foreach ($videoData as $video) {
            $this->increaseIteration();

            $prefix = "[{$video['file_title']}]";
            $this->iteration("Uploading video {$prefix}...", self::TYPE_WARNING);

            $afterUploadData = [
                'title' => $video['file_title'],
                'thumbnail' => $files[$video['file']]['thumbnail'] ?? 'Not  Found',
                'folder' => $files[$video['file']]['folder'] ?? 'Not  Found',
                'uploaded' => 0,
                'filecode' => 'Not Found',
            ];

            $response = $doodStream->uploadFileToServer($uploadServer, $video);
            if (!$response->isSuccesful()) {
                $this->iteration("[DOODSTREAM] Failed to upload video {$prefix} with error: {$response->getMessage()}...", self::TYPE_ERROR);

                $afterUploads[] = $afterUploadData;
                $failed++;
                continue;
            }

            $afterUploadData = array_merge($afterUploadData, [
                'uploaded' => 1,
                'filecode' => $response->getData('result.0.filecode'),
            ]);
            $success++;

            $this->iteration("Successfully uploaded video {$prefix}...", self::TYPE_SUCCESS);

            // Move file to folder
            $moveFileResponse = $doodStream->moveFileToFolder([
                'fld_id' => $newFolderId,
                'file_code' => $response->getData('result.0.filecode'),
            ]);

            if (!$moveFileResponse->isSuccesful()) {
                $this->iteration("[DOODSTREAM] Failed to move video {$prefix} to today folder with error: {$moveFileResponse->getMessage()}...", self::TYPE_WARNING);
            } else {
                $this->iteration("[DOODSTREAM] Successfully moved video {$prefix} to today folder...", self::TYPE_SUCCESS);
            }

            $this->line();
        }

        $this->iteration("[LULUSTREAM] Generating CSV file for today upload...", self::TYPE_WARNING);

        $today = Carbon::today()->format('dmY-His');
        $todayFolderName = dirname(dirname(__DIR__)) . "\assets\\{$today}";

        try {
            $csv = Writer::createFromPath("{$todayFolderName}\summary-dood-{$today}.csv", 'w+');
            $csv->insertOne($csvHeaders);
    
            foreach ($afterUploads as $uploaded) {
                $csv->insertOne([
                    $uploaded['folder'],
                    $uploaded['filecode'],
                    $uploaded['title'],
                    $uploaded['thumbnail'],
                ]);
            }
    
            $csv->output("{$todayFolderName}\summary-dood-{$today}.csv");
        } catch (Exception $e) {

        }

        return [$success, $failed];
    }
    
    private function handleLuluStream(array $files): array
    {
        $success = 0;
        $failed = 0;
        $this->warning('STARTING UPLOAD PROCESS FOR LULUSTREAM...');
        $luluStream = new LuluStreamModule();

        //Create a new folder for today
        $newFolderResponse = $luluStream->createFolder([
            'name' => Carbon::now()->format('Y m d H:i:s'),
            'descr' => "This folder contains videos uploaded starting at: " . Carbon::now()->format('d/m/Y H:i:s'),
        ]);

        if (!$newFolderResponse->isSuccesful()) {
            $this->error("[LULUSTREAM] Failed to create folder for today. Error: {$newFolderResponse->getMessage()}...");
            $failed++;

            return [$success, $failed];
        }

        $newFolderId = $newFolderResponse->getData('result.fld_id');

        // Get upload server
        $uploadServerResponse = $luluStream->getUploadServer();

        if (!$uploadServerResponse->isSuccesful()) {
            $this->error("[LULUSTREAM] Failed to get upload server for today. Error: {$uploadServerResponse->getMessage()}...");
            $failed++;

            return [$success, $failed];
        }

        $uploadServer = $uploadServerResponse->getData('result');
      
        $videoData = collect($files)
            ->map(function ($video) use ($newFolderId) {
                return [
                    'file' => $video['location'],
                    'file_title' => $video['title'],
                    'file_public' => 1,
                    'html_redirect' => 0,
                    'fld_id' => $newFolderId ?? 1,
                ];
            })
            ->toArray();


        // Create CSV file
        $csvHeaders = [
            'Folder',
            'Video Code',
            'Title',
            'Thumbnail',
        ];
        $afterUploads = [];

        foreach ($videoData as $video) {
            $this->increaseIteration();

            $prefix = "[{$video['file_title']}]";
            $this->iteration("Uploading video {$prefix}...", self::TYPE_WARNING);

            $afterUploadData = [
                'title' => $video['file_title'],
                'thumbnail' => $files[$video['file']]['thumbnail'] ?? 'Not  Found',
                'folder' => $files[$video['file']]['folder'] ?? 'Not  Found',
                'uploaded' => 0,
                'filecode' => 'Not Found',
            ];

            $response = $luluStream->uploadFileToServer($uploadServer, $video);
            if (!$response->isSuccesful()) {
                $this->iteration("[LULUSTREAM] Failed to upload video {$prefix} with error: {$response->getMessage()}...", self::TYPE_ERROR);

                $afterUploads[] = $afterUploadData;
                $failed++;
                continue;
            }

            $afterUploadData = array_merge($afterUploadData, [
                'uploaded' => 1,
                'filecode' => $response->getData('files.0.filecode'),
            ]);
            $success++;

            $this->iteration("[LULUSTREAM] Successfully uploaded video {$prefix}...", self::TYPE_SUCCESS);
        }

        $this->iteration("[LULUSTREAM] Generating CSV file for today upload...", self::TYPE_WARNING);

        $today = Carbon::today()->format('dmY-His');
        $todayFolderName = dirname(dirname(__DIR__)) . "\assets\\{$today}";

        try {
            $csv = Writer::createFromPath("{$todayFolderName}\summary-lulu-{$today}.csv", 'w+');
            $csv->insertOne($csvHeaders);
    
            foreach ($afterUploads as $uploaded) {
                $csv->insertOne([
                    $uploaded['folder'],
                    $uploaded['filecode'],
                    $uploaded['title'],
                    $uploaded['thumbnail'],
                ]);
            }
    
            $csv->output("{$todayFolderName}\summary-lulu-{$today}.csv");
        } catch (Exception $e) {

        }

        return [$success, $failed];
    }
}

$script = new MultipleUploadVideos($argv);

return $script->handle();