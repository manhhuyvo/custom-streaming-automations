<?php

namespace StreamingAutomations\Configs;

interface ConfigInterface
{
    public static function all(): array;

    public static function getSimpleName(): string;
}