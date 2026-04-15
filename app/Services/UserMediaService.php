<?php

namespace App\Services;

use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;

class UserMediaService
{
    public function storeUserImage(UploadedFile $file, int $userId): string
    {
        return $file->store("avatars/users/{$userId}", 'public');
    }

    public function storeNomineeImage(UploadedFile $file, int $userId): string
    {
        return $file->store("avatars/nominees/{$userId}", 'public');
    }

    public function deleteIfExists(?string $path): void
    {
        if ($path && Storage::disk('public')->exists($path)) {
            Storage::disk('public')->delete($path);
        }
    }
}
