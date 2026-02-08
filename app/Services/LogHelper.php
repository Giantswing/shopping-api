<?php

namespace App\Services;

use Illuminate\Support\Facades\Log;

class LogHelper
{
    public static function info(string $class, string $method, int $line, string $message): void
    {
        Log::info(self::format($class, $method, $line, $message));
    }

    public static function error(string $class, string $method, int $line, string $message): void
    {
        Log::error(self::format($class, $method, $line, $message));
    }

    public static function warning(string $class, string $method, int $line, string $message): void
    {
        Log::warning(self::format($class, $method, $line, $message));
    }

    public static function debug(string $class, string $method, int $line, string $message): void
    {
        Log::debug(self::format($class, $method, $line, $message));
    }

    private static function format(string $class, string $method, int $line, string $message): string
    {
        return class_basename($class) . ', ' . $method . ', line ' . $line . ': ' . $message;
    }
}
