<?php

namespace App\Support;

use Carbon\CarbonInterface;

class BrandedMail
{
    public static function logoPath(): string
    {
        $mailLogo = public_path('images/logo-mail.png');

        return is_file($mailLogo)
            ? $mailLogo
            : public_path('images/logo.png');
    }

    public static function logoUrl(mixed $message = null): string
    {
        $path = self::logoPath();

        if (is_object($message) && method_exists($message, 'embed')) {
            try {
                return $message->embed($path);
            } catch (\Throwable) {
                // Fall through to a public URL / data URI.
            }
        }

        // Inline when possible so previews and clients never depend on a
        // not-yet-deployed remote asset (logo-mail.png).
        if (is_file($path) && filesize($path) <= 40_000) {
            $mime = mime_content_type($path) ?: 'image/png';
            $data = base64_encode((string) file_get_contents($path));

            return 'data:'.$mime.';base64,'.$data;
        }

        $base = rtrim((string) config('app.url'), '/');
        $host = parse_url($base, PHP_URL_HOST);

        if (! is_string($host) || in_array($host, ['localhost', '127.0.0.1', '::1'], true)) {
            $base = 'https://shopnernir.com';
        }

        $file = str_ends_with($path, 'logo-mail.png') ? 'logo-mail.png' : 'logo.png';

        return $base.'/images/'.$file;
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
