<?php

namespace App\Models;

use Carbon\Carbon;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasManyThrough;

class Investment extends Model
{
    /**
     * Last calendar month (inclusive) that automatic accruals fill up to — always the current month.
     */
    public static function accrualThroughInclusive(): Carbon
    {
        return Carbon::now()->startOfMonth();
    }

    public const STATUS_DRAFT = 'draft';

    public const STATUS_ACTIVE = 'active';

    public const STATUS_CLOSED = 'closed';

    /**
     * @var array<string, mixed>
     */
    protected $attributes = [
        'is_active' => true,
    ];

    protected $fillable = [
        'title',
        'notes',
        'period_start',
        'default_monthly_rate_pct',
        'status',
        'is_active',
        'created_by',
    ];

    protected function casts(): array
    {
        return [
            'period_start' => 'date',
            'default_monthly_rate_pct' => 'decimal:4',
            'is_active' => 'boolean',
        ];
    }

    public function creator(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    public function participants(): HasMany
    {
        return $this->hasMany(InvestmentParticipant::class);
    }

    public function documents(): HasMany
    {
        return $this->hasMany(InvestmentDocument::class);
    }

    public function periods(): HasMany
    {
        return $this->hasMany(InvestmentPeriod::class)->orderBy('month');
    }

    /**
     * Participant rows on each accrual period (for aggregates scoped by user_id).
     */
    public function periodUserShares(): HasManyThrough
    {
        return $this->hasManyThrough(
            InvestmentPeriodUser::class,
            InvestmentPeriod::class,
            'investment_id',
            'investment_period_id',
            'id',
            'id'
        );
    }

    public function totalContributions(): string
    {
        return (string) $this->participants()->sum('contribution_amount');
    }

    /**
     * First calendar month for accruals.
     * If {@see $period_start} is set, that month is used exactly (including earlier than
     * the record’s creation month, for backdated pools). If it is null, the month of
     * {@see $created_at} is used.
     */
    public function firstAccrualMonthStart(): Carbon
    {
        if ($this->period_start === null) {
            return Carbon::parse($this->created_at)->startOfMonth();
        }

        return Carbon::parse($this->period_start)->startOfMonth();
    }
}
