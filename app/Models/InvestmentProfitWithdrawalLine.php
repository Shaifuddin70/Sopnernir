<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class InvestmentProfitWithdrawalLine extends Model
{
    protected $fillable = [
        'withdrawal_id',
        'user_id',
        'contribution_amount',
        'amount',
    ];

    protected function casts(): array
    {
        return [
            'contribution_amount' => 'decimal:2',
            'amount' => 'decimal:2',
        ];
    }

    public function withdrawal(): BelongsTo
    {
        return $this->belongsTo(InvestmentProfitWithdrawal::class, 'withdrawal_id');
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }
}
