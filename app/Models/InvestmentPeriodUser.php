<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class InvestmentPeriodUser extends Model
{
    protected $fillable = [
        'investment_period_id',
        'user_id',
        'contribution_snapshot',
        'profit_share',
    ];

    protected function casts(): array
    {
        return [
            'contribution_snapshot' => 'decimal:2',
            'profit_share' => 'decimal:2',
        ];
    }

    public function period(): BelongsTo
    {
        return $this->belongsTo(InvestmentPeriod::class, 'investment_period_id');
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }
}
