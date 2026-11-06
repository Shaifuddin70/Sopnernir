<?php

namespace App\Support;

use Carbon\CarbonInterface;

class BrandedMail
{
    public static function logoUrl(mixed $message = null): string
    {
        $base = rtrim((string) config('app.url'), '/');
        $host = parse_url($base, PHP_URL_HOST);

        if (! is_string($host) || in_array($host, ['localhost', '127.0.0.1', '::1'], true)) {
            $base = 'https://shopnernir.com';
        }

        return $base.'/images/logo.png';
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
