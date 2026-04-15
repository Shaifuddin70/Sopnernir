<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class InvestmentParticipant extends Model
{
    protected $fillable = [
        'investment_id',
        'user_id',
        'contribution_amount',
    ];

    protected function casts(): array
    {
        return [
            'contribution_amount' => 'decimal:2',
        ];
    }

    public function investment(): BelongsTo
    {
        return $this->belongsTo(Investment::class);
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }
}
