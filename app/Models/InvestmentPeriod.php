<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class InvestmentPeriod extends Model
{
    protected $fillable = [
        'investment_id',
        'month',
        'applied_rate_pct',
        'principal_snapshot',
        'profit_amount',
    ];

    protected function casts(): array
    {
        return [
            'month' => 'date',
            'applied_rate_pct' => 'decimal:4',
            'principal_snapshot' => 'decimal:2',
            'profit_amount' => 'decimal:2',
        ];
    }

    public function investment(): BelongsTo
    {
        return $this->belongsTo(Investment::class);
    }

    public function periodUsers(): HasMany
    {
        return $this->hasMany(InvestmentPeriodUser::class);
    }
}
