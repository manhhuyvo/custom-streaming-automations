<?php

namespace StreamingAutomations\Base;
require 'src/custom-autoload.php';

use Illuminate\Support\Str;

class BaseScript
{
    public const DRY_MODE = '--dry';

    public const TYPE_SUCCESS = 'success';
    public const TYPE_ERROR = 'error';
    public const TYPE_WARNING = 'warning';

    private bool $isDry = false;

    private int $iteration = 0;

    private int $currentIndex = 0;

    public function __construct($argv) {
        $validArguments = collect($argv)
            ->filter(function ($arg) {
                return Str::startsWith($arg, '--');
            })
            ->toArray();

        $this->setIsDry(in_array(self::DRY_MODE, $validArguments));
    }

    public function setIteration(int $total): void
    {
        $this->iteration = ! empty($total) ? $total : 1;
    }

    public function increaseIteration(): void
    {
        $this->currentIndex++;
    }

    public function iteration(string $content, string $type = ''): void
    {
        $iterationMessage = "[{$this->currentIndex}/{$this->iteration}]";

        switch ($type) {
            case self::TYPE_SUCCESS:
                $this->success("{$iterationMessage} {$content}");

                return;
            case self::TYPE_ERROR:
                $this->error("{$iterationMessage} {$content}");

                return;                
            case self::TYPE_WARNING:
                $this->warning("{$iterationMessage} {$content}");

                return;
            default:
                $dryMessage = $this->isDry() ? '[DRY MODE]' : '';
                $this->output("{$dryMessage} {$iterationMessage} {$content}");

                return;
        }
    }

    public function success(string $value = ''): void
    {
        $this->output($this->getSuccessMessage($value));
    }

    public function error(string $value = ''): void
    {
        $this->output($this->getErrorMessage($value));
    }    

    public function warning(string $value = ''): void
    {
        $this->output($this->getWarningMessage($value));
    }

    public function output(string $value): void
    {
        echo "{$value}\n";
    }

    public function setIsDry(bool $value = false): void
    {
        $this->isDry = $value;   
    }

    public function isDry(): bool
    {
        return $this->isDry;
    }

    private function getSuccessMessage(string $value = ''): string
    {
        return $this->isDry()
            ? "\e[32m[DRY MODE] {$value}"
            : "\e[32m{$value}";
    }

    private function getErrorMessage(string $value = ''): string
    {
        return $this->isDry()
            ? "\e[31m[DRY MODE] {$value}"
            : "\e[31m{$value}";
    }

    private function getWarningMessage(string $value = ''): string
    {
        return $this->isDry()
            ? "\e[33m[DRY MODE] {$value}"
            : "\e[33m{$value}";
    }
}