<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Notice extends Model
{
    public const STATUS_DRAFT = 'draft';

    public const STATUS_PUBLISHED = 'published';

    protected $fillable = [
        'title',
        'body',
        'status',
        'is_pinned',
        'published_at',
        'emailed_at',
        'created_by',
    ];

    protected function casts(): array
    {
        return [
            'is_pinned' => 'boolean',
            'published_at' => 'datetime',
            'emailed_at' => 'datetime',
        ];
    }

    public function author(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    public function attachments(): HasMany
    {
        return $this->hasMany(NoticeAttachment::class)->orderBy('id');
    }

    public function readers(): BelongsToMany
    {
        return $this->belongsToMany(User::class, 'notice_reads')
            ->withPivot('read_at')
            ->withTimestamps();
    }

    public function isPublished(): bool
    {
        return $this->status === self::STATUS_PUBLISHED;
    }

    public function isDraft(): bool
    {
        return $this->status === self::STATUS_DRAFT;
    }

    public function scopePublished(Builder $query): Builder
    {
        return $query->where('status', self::STATUS_PUBLISHED);
    }

    public function scopeBoardOrder(Builder $query): Builder
    {
        return $query
            ->orderByDesc('is_pinned')
            ->orderByDesc('published_at')
            ->orderByDesc('id');
    }

    public function excerpt(int $limit = 160): string
    {
        $text = trim(preg_replace('/\s+/u', ' ', html_entity_decode(strip_tags($this->body))) ?? '');

        if ($text === '') {
            return '';
        }

        return mb_strlen($text) > $limit
            ? rtrim(mb_substr($text, 0, $limit - 1)).'…'
            : $text;
    }

    public function isReadBy(?User $user): bool
    {
        if ($user === null) {
            return false;
        }

        if (array_key_exists('is_read', $this->attributes)) {
            return (bool) $this->attributes['is_read'];
        }

        return $this->readers()->where('user_id', $user->id)->exists();
    }
}
