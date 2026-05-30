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
        'deed_completion_deadline',
        'default_monthly_rate_pct',
        'status',
        'is_active',
        'created_by',
    ];

    protected function casts(): array
    {
        return [
            'period_start' => 'date',
            'deed_completion_deadline' => 'date',
            'default_monthly_rate_pct' => 'decimal:4',
            'is_active' => 'boolean',
        ];
    }

    /**
     * Calendar date when this investment plan ends (stored as deed_completion_deadline).
     */
    public function planCompletionDate(): ?Carbon
    {
        if ($this->deed_completion_deadline === null) {
            return null;
        }

        return Carbon::parse($this->deed_completion_deadline)->startOfDay();
    }

    /**
     * Last calendar month that may receive profit accruals (month of the plan completion date).
     */
    public function planCompletionMonthStart(): ?Carbon
    {
        return $this->planCompletionDate()?->copy()->startOfMonth();
    }

    /**
     * Latest month automatic accruals may fill for this pool (current month, capped by plan end).
     */
    public function lastAccrualMonthInclusive(): Carbon
    {
        $through = static::accrualThroughInclusive();
        $planEndMonth = $this->planCompletionMonthStart();

        if ($planEndMonth !== null && $planEndMonth->lt($through)) {
            return $planEndMonth->copy();
        }

        return $through;
    }

    /**
     * Whether the plan completion date is before today — accruals stop and the pool is treated as finished.
     */
    public function hasPlanCompleted(): bool
    {
        $completion = $this->planCompletionDate();

        if ($completion === null) {
            return false;
        }

        return $completion->lt(Carbon::today());
    }

    /** @deprecated Use {@see hasPlanCompleted()} */
    public function isDeedDeadlinePassed(): bool
    {
        return $this->hasPlanCompleted();
    }

    /**
     * Signed days until plan completion (negative after completion). Null when no date is set.
     */
    public function planCompletionDaysRemaining(): ?int
    {
        $completion = $this->planCompletionDate();

        if ($completion === null) {
            return null;
        }

        return (int) Carbon::today()->diffInDays($completion, false);
    }

    /** @deprecated Use {@see planCompletionDaysRemaining()} */
    public function deedDeadlineDaysRemaining(): ?int
    {
        return $this->planCompletionDaysRemaining();
    }

    public function acceptsNewAccruals(): bool
    {
        return $this->status === self::STATUS_ACTIVE
            && $this->is_active
            && ! $this->hasPlanCompleted();
    }

    /**
     * Close active pools whose plan completion date has passed.
     */
    public static function closePlansPastCompletion(): int
    {
        return static::query()
            ->where('status', self::STATUS_ACTIVE)
            ->whereNotNull('deed_completion_deadline')
            ->whereDate('deed_completion_deadline', '<', Carbon::today())
            ->update([
                'status' => self::STATUS_CLOSED,
                'is_active' => false,
            ]);
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
