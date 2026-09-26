<?php

namespace App\Helpers;

use Carbon\Carbon;

class TimezoneHelper
{
    public static function getSchoolTimezone($schoolPartner = null)
    {
        return $schoolPartner?->timezone ?: config('app.timezone');
    }

    public static function getTimezoneLabel($timezone = null)
    {
        $timezone = $timezone ?: config('app.timezone');

        return match ($timezone) {
            'Asia/Jakarta' => 'WIB',
            'Asia/Makassar' => 'WITA',
            'Asia/Jayapura' => 'WIT',
            default => $timezone,
        };
    }

    public static function parse($date, $timezone = null)
    {
        if (!$date) {
            return null;
        }

        $timezone = $timezone ?: config('app.timezone');

        if ($date instanceof Carbon) {
            $date = $date->format('Y-m-d H:i:s');
        }

        return Carbon::createFromFormat('Y-m-d H:i:s', $date, $timezone);
    }

    public static function format($date, $timezone = null, $format = 'Y-m-d H:i')
    {
        return self::parse($date, $timezone)?->format($format);
    }

    public static function formatDate($date, $timezone = null, $format = 'Y-m-d')
    {
        return self::parse($date, $timezone)?->format($format);
    }

    public static function formatTime($date, $timezone = null, $format = 'H:i')
    {
        return self::parse($date, $timezone)?->format($format);
    }

    public static function formatIso($date, $timezone = null)
    {
        return self::parse($date, $timezone)?->format('Y-m-d\TH:i:sP');
    }

    public static function diffInSeconds($startDate, $endDate, $timezone = null)
    {
        $start = self::parse($startDate, $timezone);
        $end = self::parse($endDate, $timezone);

        if (!$start || !$end) {
            return null;
        }

        return $start->diffInSeconds($end);
    }

    public static function now($timezone = null)
    {
        return Carbon::now(
            $timezone ?: config('app.timezone')
        );
    }
}