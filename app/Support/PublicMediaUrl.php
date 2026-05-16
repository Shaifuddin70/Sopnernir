<?php

namespace App\Support;

use Illuminate\Support\Facades\Storage;

final class PublicMediaUrl
{
    /**
     * URL for files stored relative to disks `web_public` (public/media/*) or legacy `public`.
     */
    public static function forPath(?string $path): ?string
    {
        if ($path === null || $path === '') {
            return null;
        }

        if (Storage::disk('web_public')->exists($path)) {
            return asset('media/'.$path);
        }

        if (Storage::disk('public')->exists($path) && file_exists(public_path('storage'))) {
            return asset('storage/'.$path);
        }

        return null;
    }
}
