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
        'deed_no',
        'notes',
        'period_start',
        'tagged_payment_month',
        'deed_completion_deadline',
        'default_monthly_rate_pct',
        'total_profit_amount',
        'total_invested_amount',
        'contribution_per_investor',
        'status',
        'is_active',
        'created_by',
    ];

    protected function casts(): array
    {
        return [
            'period_start' => 'date',
            'tagged_payment_month' => 'date',
            'deed_completion_deadline' => 'date',
            'default_monthly_rate_pct' => 'decimal:4',
            'total_profit_amount' => 'decimal:2',
            'total_invested_amount' => 'decimal:2',
            'contribution_per_investor' => 'decimal:2',
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

    /**
     * Number of calendar accrual months from the first accrual month through plan completion.
     */
    public function planAccrualMonthCount(): int
    {
        $startMonth = $this->firstAccrualMonthStart()->copy()->startOfMonth();
        $endMonth = $this->planCompletionMonthStart();

        if ($endMonth === null || $startMonth->gt($endMonth)) {
            return 0;
        }

        $count = 0;
        for ($month = $startMonth->copy(); $month->lte($endMonth); $month->addMonth()) {
            $count++;
        }

        return $count;
    }

    public function planStartDay(): Carbon
    {
        if ($this->period_start !== null) {
            return Carbon::parse($this->period_start)->startOfDay();
        }

        return $this->firstAccrualMonthStart()->copy()->startOfDay();
    }

    /**
     * Calendar days from plan start through plan end (same basis as daily profit display).
     */
    public function planDayCount(): int
    {
        $end = $this->planCompletionDate();
        if ($end === null) {
            return 0;
        }

        $start = $this->planStartDay();
        if ($start->gt($end)) {
            return 0;
        }

        return max(1, (int) $start->diffInDays($end));
    }

    public function dailyPoolProfitFromTotal(): ?float
    {
        if (! $this->usesTotalProfitPlan()) {
            return null;
        }

        $days = $this->planDayCount();

        return $days > 0 ? (float) $this->total_profit_amount / $days : null;
    }

    /**
     * Plan days elapsed from start through $date (capped by plan end).
     */
    public function profitDaysElapsedThrough(Carbon $date): int
    {
        $start = $this->planStartDay();
        $end = $this->planCompletionDate();

        if ($end === null || $date->lt($start)) {
            return 0;
        }

        $through = $date->copy()->startOfDay();
        if ($through->gt($end)) {
            $through = $end->copy();
        }

        return max(0, min($this->planDayCount(), (int) $start->diffInDays($through)));
    }

    /**
     * Cumulative pool profit earned through $date on a daily accrual basis.
     */
    public function cumulativeProfitThroughDate(Carbon $date): float
    {
        $daily = $this->dailyPoolProfitFromTotal();
        if ($daily === null) {
            return 0.0;
        }

        $days = $this->profitDaysElapsedThrough($date);

        return min((float) $this->total_profit_amount, round($daily * $days, 2));
    }

    /**
     * Pool profit for one calendar month (variable when the plan starts or ends mid-month).
     */
    public function poolProfitForMonth(Carbon $month): float
    {
        if (! $this->usesTotalProfitPlan()) {
            return 0.0;
        }

        $monthStart = $month->copy()->startOfMonth()->startOfDay();
        $monthEnd = $month->copy()->endOfMonth()->startOfDay();
        $beforeMonth = $monthStart->copy()->subDay();

        return round(
            $this->cumulativeProfitThroughDate($monthEnd) - $this->cumulativeProfitThroughDate($beforeMonth),
            2
        );
    }

    public function usesTotalProfitPlan(): bool
    {
        return $this->total_profit_amount !== null && (float) $this->total_profit_amount > 0;
    }

    /**
     * Even split of planned total profit across accrual months (informational average).
     */
    public function averageMonthlyProfitAmount(): ?float
    {
        if (! $this->usesTotalProfitPlan()) {
            return null;
        }

        $months = $this->planAccrualMonthCount();

        return $months > 0
            ? round((float) $this->total_profit_amount / $months, 2)
            : null;
    }
}
