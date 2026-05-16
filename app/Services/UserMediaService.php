<?php

namespace App\Services;

use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;

class UserMediaService
{
    public function storeUserImage(UploadedFile $file, int $userId): string
    {
        return $file->store("avatars/users/{$userId}", 'web_public');
    }

    public function storeNomineeImage(UploadedFile $file, int $userId): string
    {
        return $file->store("avatars/nominees/{$userId}", 'web_public');
    }

    public function deleteIfExists(?string $path): void
    {
        if (! $path) {
            return;
        }

        if (Storage::disk('web_public')->exists($path)) {
            Storage::disk('web_public')->delete($path);
        }

        if (Storage::disk('public')->exists($path)) {
            Storage::disk('public')->delete($path);
        }
    }
}
