<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class InvestmentDocument extends Model
{
    protected $fillable = [
        'investment_id',
        'disk',
        'path',
        'mime',
        'original_name',
    ];

    public function investment(): BelongsTo
    {
        return $this->belongsTo(Investment::class);
    }
}
