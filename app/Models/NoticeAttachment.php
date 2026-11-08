<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class NoticeAttachment extends Model
{
    protected $fillable = [
        'notice_id',
        'disk',
        'path',
        'mime',
        'size',
        'original_name',
    ];

    protected function casts(): array
    {
        return [
            'size' => 'integer',
        ];
    }

    public function notice(): BelongsTo
    {
        return $this->belongsTo(Notice::class);
    }

    public function humanSize(): string
    {
        $bytes = max(0, (int) $this->size);

        if ($bytes < 1024) {
            return $bytes.' B';
        }

        if ($bytes < 1024 * 1024) {
            return number_format($bytes / 1024, 1).' KB';
        }

        return number_format($bytes / (1024 * 1024), 1).' MB';
    }
}
