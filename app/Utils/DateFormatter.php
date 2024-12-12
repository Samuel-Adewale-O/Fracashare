<?php

namespace App\Utils;

use Carbon\Carbon;

class DateFormatter
{
    public static function formatDateTime(string $datetime, string $timezone = null): string
    {
        $carbon = Carbon::parse($datetime);
        if ($timezone) {
            $carbon->setTimezone($timezone);
        }
        return $carbon->format('Y-m-d H:i:s');
    }

    public static function formatHuman(string $datetime): string
    {
        return Carbon::parse($datetime)->diffForHumans();
    }

    public static function isExpired(string $datetime): bool
    {
        return Carbon::parse($datetime)->isPast();
    }
}