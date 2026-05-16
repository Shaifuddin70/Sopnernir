<?php

namespace App\Models;

use App\Support\PublicMediaUrl;
use Database\Factories\NomineeFactory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Nominee extends Model
{
    /** @use HasFactory<NomineeFactory> */
    use HasFactory;
    protected $fillable = [
        'user_id',
        'name',
        'email',
        'phone',
        'address',
        'image',
    ];

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function profileImageUrl(): ?string
    {
        return PublicMediaUrl::forPath($this->image);
    }
}
