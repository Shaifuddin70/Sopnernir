<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class InvestmentProfitWithdrawal extends Model
{
    protected $fillable = [
        'batch_id',
        'investment_id',
        'through_date',
        'amount',
        'profit_through_date',
        'notes',
        'withdrawn_by',
        'withdrawn_at',
    ];

    protected function casts(): array
    {
        return [
            'through_date' => 'date',
            'amount' => 'decimal:2',
            'profit_through_date' => 'decimal:2',
            'withdrawn_at' => 'datetime',
        ];
    }

    public function batch(): BelongsTo
    {
        return $this->belongsTo(InvestmentProfitWithdrawalBatch::class, 'batch_id');
    }

    public function investment(): BelongsTo
    {
        return $this->belongsTo(Investment::class);
    }

    public function withdrawnBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'withdrawn_by');
    }

    public function lines(): HasMany
    {
        return $this->hasMany(InvestmentProfitWithdrawalLine::class, 'withdrawal_id');
    }
}
