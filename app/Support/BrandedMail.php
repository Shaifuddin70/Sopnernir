<?php

namespace App\Support;

use Carbon\CarbonInterface;

class BrandedMail
{
    public static function logoUrl(mixed $message = null): string
    {
        $path = public_path('images/logo.jpg');
        $fallback = asset('images/logo.jpg');

        if (is_object($message) && method_exists($message, 'embed') && is_file($path)) {
            try {
                return $message->embed($path);
            } catch (\Throwable) {
                return $fallback;
            }
        }

        return $fallback;
    }

    public static function money(mixed $amount): string
    {
        if ($amount === null || $amount === '') {
            return '—';
        }

        return number_format((float) $amount, 2);
    }

    public static function monthDuration(?CarbonInterface $start, ?CarbonInterface $end): ?string
    {
        if ($start === null || $end === null) {
            return null;
        }

        $months = max(1, (int) $start->copy()->startOfDay()->diffInMonths($end->copy()->startOfDay()));

        return $months === 1
            ? __('1 month')
            : __(':count months', ['count' => $months]);
    }
}
