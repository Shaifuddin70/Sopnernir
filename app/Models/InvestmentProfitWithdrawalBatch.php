<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class InvestmentProfitWithdrawalBatch extends Model
{
    protected $fillable = [
        'through_date',
        'amount',
        'pools_count',
        'notes',
        'withdrawn_by',
        'withdrawn_at',
    ];

    protected function casts(): array
    {
        return [
            'through_date' => 'date',
            'amount' => 'decimal:2',
            'pools_count' => 'integer',
            'withdrawn_at' => 'datetime',
        ];
    }

    public function withdrawnBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'withdrawn_by');
    }

    public function withdrawals(): HasMany
    {
        return $this->hasMany(InvestmentProfitWithdrawal::class, 'batch_id');
    }
}
