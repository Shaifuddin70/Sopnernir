<?php

namespace App\Services;

use App\Models\Investment;
use App\Models\InvestmentParticipant;
use App\Models\InvestmentPeriod;
use App\Models\InvestmentPeriodUser;
use Carbon\Carbon;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use InvalidArgumentException;

class InvestmentAccrualService
{
    /**
     * Create accrual rows for each missing calendar month from the pool’s first
     * accrual month through $through (inclusive), using the pool default rate.
     *
     * @param  (callable(Investment, Carbon, InvalidArgumentException): void)|null  $onSkippedMonth
     * @return int Number of new accrual months recorded
     */
    public function fillMissingMonthsThrough(Investment $investment, Carbon $through, ?callable $onSkippedMonth = null): int
    {
        if ($investment->status !== Investment::STATUS_ACTIVE || ! $investment->is_active) {
            return 0;
        }

        $through = $this->cappedAccrualThrough($investment, $through);
        $poolStart = $investment->firstAccrualMonthStart()->copy()->startOfMonth();

        if ($poolStart->gt($through)) {
            return 0;
        }

        $recorded = 0;

        for ($m = $poolStart->copy(); $m->lte($through); $m->addMonth()) {
            if (InvestmentPeriod::query()
                ->where('investment_id', $investment->id)
                ->whereDate('month', $m)
                ->exists()) {
                continue;
            }

            try {
                $this->accrue($investment, $m->copy(), null);
                $recorded++;
            } catch (InvalidArgumentException $e) {
                Log::info('Investment accrual skipped', [
                    'investment_id' => $investment->id,
                    'month' => $m->format('Y-m'),
                    'message' => $e->getMessage(),
                ]);
                if ($onSkippedMonth !== null) {
                    $onSkippedMonth($investment, $m->copy()->startOfMonth(), $e);
                }
            }
        }

        return $recorded;
    }

    /**
     * Ensure every calendar month from the pool start through $through reflects the current
     * participant set. Existing months are recalculated in place; missing months are created.
     *
     * @param  (callable(Investment, Carbon, InvalidArgumentException): void)|null  $onSkippedMonth
     * @return array{created: int, recalculated: int}
     */
    public function syncMonthsThrough(Investment $investment, Carbon $through, ?callable $onSkippedMonth = null): array
    {
        if ($investment->status !== Investment::STATUS_ACTIVE || ! $investment->is_active) {
            return ['created' => 0, 'recalculated' => 0];
        }

        $through = $this->cappedAccrualThrough($investment, $through);
        $poolStart = $investment->firstAccrualMonthStart()->copy()->startOfMonth();

        if ($poolStart->gt($through)) {
            return ['created' => 0, 'recalculated' => 0];
        }

        $created = 0;
        $recalculated = 0;

        for ($m = $poolStart->copy(); $m->lte($through); $m->addMonth()) {
            $existingPeriod = InvestmentPeriod::query()
                ->where('investment_id', $investment->id)
                ->whereDate('month', $m)
                ->first();

            try {
                if ($existingPeriod) {
                    $this->recalculatePeriod($existingPeriod);
                    $recalculated++;
                } else {
                    $this->accrue($investment, $m->copy(), null);
                    $created++;
                }
            } catch (InvalidArgumentException $e) {
                Log::info('Investment accrual sync skipped', [
                    'investment_id' => $investment->id,
                    'month' => $m->format('Y-m'),
                    'message' => $e->getMessage(),
                ]);
                if ($onSkippedMonth !== null) {
                    $onSkippedMonth($investment, $m->copy()->startOfMonth(), $e);
                }
            }
        }

        return ['created' => $created, 'recalculated' => $recalculated];
    }

    /**
     * @return InvestmentPeriod
     *
     * @throws InvalidArgumentException
     */
    public function accrue(Investment $investment, Carbon $month, ?string $rateOverridePct): InvestmentPeriod
    {
        $monthStart = $month->copy()->startOfMonth();

        if (! $investment->is_active) {
            throw new InvalidArgumentException(__(
                'This investment is inactive. Activate it before recording accruals.'
            ));
        }

        if (InvestmentPeriod::query()
            ->where('investment_id', $investment->id)
            ->whereDate('month', $monthStart)
            ->exists()) {
            throw new InvalidArgumentException('Accrual for this month already exists.');
        }

        $poolStart = $investment->firstAccrualMonthStart()->copy()->startOfMonth();

        if ($monthStart->lt($poolStart)) {
            throw new InvalidArgumentException(__(
                'Cannot accrue for a month before this pool first accrual month (:month).',
                ['month' => $poolStart->translatedFormat('F Y')]
            ));
        }

        $this->assertMonthWithinPlan($investment, $monthStart);

        $participants = $investment->participants()->orderBy('id')->get();

        if ($participants->isEmpty()) {
            throw new InvalidArgumentException(__(
                'Add at least one tagged investor with a contribution before recording an accrual.'
            ));
        }

        $principal = $participants->sum(fn (InvestmentParticipant $p) => (float) $p->contribution_amount);

        if ($principal <= 0) {
            throw new InvalidArgumentException(__(
                'Total tagged contributions for this month sum to zero. Set contribution amounts above zero for each investor.',
            ));
        }

        $rate = $rateOverridePct !== null && $rateOverridePct !== ''
            ? (string) $rateOverridePct
            : (string) $investment->default_monthly_rate_pct;

        [$profitCents, $rate] = $this->resolveMonthlyProfit($investment, $principal, $rate, $monthStart);

        $split = $this->splitProfitCents($participants, $principal, $profitCents);
        $profitCents = $split['profit_cents'];
        $lines = $split['lines'];

        return DB::transaction(function () use ($investment, $monthStart, $rate, $principal, $profitCents, $lines) {
            $period = InvestmentPeriod::create([
                'investment_id' => $investment->id,
                'month' => $monthStart->toDateString(),
                'applied_rate_pct' => $rate,
                'principal_snapshot' => number_format($principal, 2, '.', ''),
                'profit_amount' => number_format($profitCents / 100, 2, '.', ''),
            ]);

            foreach ($lines as $line) {
                InvestmentPeriodUser::create([
                    'investment_period_id' => $period->id,
                    'user_id' => $line['user_id'],
                    'contribution_snapshot' => number_format($line['contribution'], 2, '.', ''),
                    'profit_share' => number_format($line['profit_share_cents'] / 100, 2, '.', ''),
                ]);
            }

            return $period->fresh(['periodUsers']);
        });
    }

    /**
     * Rebuild one previously posted month using the current tagged investors and the period's
     * stored applied rate.
     *
     * @throws InvalidArgumentException
     */
    public function recalculatePeriod(InvestmentPeriod $period): InvestmentPeriod
    {
        $period->loadMissing('investment');

        $investment = $period->investment;
        if (! $investment || $investment->status !== Investment::STATUS_ACTIVE || ! $investment->is_active) {
            throw new InvalidArgumentException('Only active pools can have accruals recalculated.');
        }

        $monthStart = Carbon::parse($period->month)->startOfMonth();
        $poolStart = $investment->firstAccrualMonthStart()->copy()->startOfMonth();
        if ($monthStart->lt($poolStart)) {
            throw new InvalidArgumentException(__(
                'Cannot accrue for a month before this pool first accrual month (:month).',
                ['month' => $poolStart->translatedFormat('F Y')]
            ));
        }

        $this->assertMonthWithinPlan($investment, $monthStart);

        $participants = $investment->participants()->orderBy('id')->get();
        if ($participants->isEmpty()) {
            throw new InvalidArgumentException(__(
                'Add at least one tagged investor with a contribution before recording an accrual.'
            ));
        }

        $principal = $participants->sum(fn (InvestmentParticipant $p) => (float) $p->contribution_amount);
        if ($principal <= 0) {
            throw new InvalidArgumentException(__(
                'Total tagged contributions for this month sum to zero. Set contribution amounts above zero for each investor.',
            ));
        }

        $rate = (string) $period->applied_rate_pct;
        [$profitCents, $rate] = $this->resolveMonthlyProfit($investment, $principal, $rate, $monthStart);
        $split = $this->splitProfitCents($participants, $principal, $profitCents);
        $profitCents = $split['profit_cents'];
        $lines = $split['lines'];

        return DB::transaction(function () use ($period, $principal, $profitCents, $rate, $lines) {
            $period->update([
                'applied_rate_pct' => $rate,
                'principal_snapshot' => number_format($principal, 2, '.', ''),
                'profit_amount' => number_format($profitCents / 100, 2, '.', ''),
            ]);

            $period->periodUsers()->delete();

            foreach ($lines as $line) {
                InvestmentPeriodUser::create([
                    'investment_period_id' => $period->id,
                    'user_id' => $line['user_id'],
                    'contribution_snapshot' => number_format($line['contribution'], 2, '.', ''),
                    'profit_share' => number_format($line['profit_share_cents'] / 100, 2, '.', ''),
                ]);
            }

            return $period->fresh(['periodUsers']);
        });
    }

    private function cappedAccrualThrough(Investment $investment, Carbon $through): Carbon
    {
        $through = $through->copy()->startOfMonth();
        $planEndMonth = $investment->planCompletionMonthStart();

        if ($planEndMonth !== null && $planEndMonth->lt($through)) {
            return $planEndMonth->copy();
        }

        return $through;
    }

    /**
     * @throws InvalidArgumentException
     */
    private function assertMonthWithinPlan(Investment $investment, Carbon $monthStart): void
    {
        $planEndMonth = $investment->planCompletionMonthStart();

        if ($planEndMonth === null) {
            return;
        }

        if ($monthStart->gt($planEndMonth)) {
            throw new InvalidArgumentException(__(
                'Cannot accrue after the plan completion date (:date). Last allowed month is :month.',
                [
                    'date' => $investment->planCompletionDate()->translatedFormat('j F Y'),
                    'month' => $planEndMonth->translatedFormat('F Y'),
                ]
            ));
        }
    }

    /**
     * @return array{profit_cents: int, lines: list<array{user_id: int, contribution: float, profit_share_cents: int}>}
     */
    private function splitProfitCents(Collection $participants, float $principal, int $profitCents): array
    {
        if ($profitCents <= 0) {
            return [
                'profit_cents' => 0,
                'lines' => $participants
                    ->map(fn (InvestmentParticipant $p) => [
                        'user_id' => (int) $p->user_id,
                        'contribution' => (float) $p->contribution_amount,
                        'profit_share_cents' => 0,
                    ])
                    ->values()
                    ->all(),
            ];
        }

        if ($this->participantsShareEqualContributions($participants)) {
            return $this->splitProfitCentsEqually($participants, $profitCents);
        }

        return [
            'profit_cents' => $profitCents,
            'lines' => $this->splitProfitCentsByLargestRemainder($participants, $principal, $profitCents),
        ];
    }

    private function participantsShareEqualContributions(Collection $participants): bool
    {
        if ($participants->count() <= 1) {
            return true;
        }

        $amounts = $participants
            ->map(fn (InvestmentParticipant $p) => number_format((float) $p->contribution_amount, 2, '.', ''))
            ->unique()
            ->values();

        return $amounts->count() === 1;
    }

    /**
     * @return array{profit_cents: int, lines: list<array{user_id: int, contribution: float, profit_share_cents: int}>}
     */
    private function splitProfitCentsEqually(Collection $participants, int $profitCents): array
    {
        $count = $participants->count();
        $shareCents = (int) round($profitCents / $count);
        $adjustedProfitCents = $shareCents * $count;

        return [
            'profit_cents' => $adjustedProfitCents,
            'lines' => $participants
                ->map(fn (InvestmentParticipant $participant) => [
                    'user_id' => (int) $participant->user_id,
                    'contribution' => (float) $participant->contribution_amount,
                    'profit_share_cents' => $shareCents,
                ])
                ->values()
                ->all(),
        ];
    }

    /**
     * @return list<array{user_id: int, contribution: float, profit_share_cents: int}>
     */
    private function splitProfitCentsByLargestRemainder(Collection $participants, float $principal, int $profitCents): array
    {
        $rows = $participants
            ->values()
            ->map(function (InvestmentParticipant $participant) use ($principal, $profitCents) {
                $contrib = (float) $participant->contribution_amount;
                $exact = $profitCents * ($contrib / $principal);
                $floor = (int) floor($exact);

                return [
                    'user_id' => (int) $participant->user_id,
                    'contribution' => $contrib,
                    'profit_share_cents' => $floor,
                    'fraction' => $exact - $floor,
                ];
            })
            ->all();

        $allocated = array_sum(array_column($rows, 'profit_share_cents'));
        $remainder = $profitCents - $allocated;

        if ($remainder > 0) {
            usort($rows, function (array $a, array $b): int {
                if ($a['fraction'] !== $b['fraction']) {
                    return $b['fraction'] <=> $a['fraction'];
                }

                return $a['user_id'] <=> $b['user_id'];
            });

            for ($i = 0; $i < $remainder; $i++) {
                $rows[$i]['profit_share_cents']++;
            }
        }

        $sharesByUserId = collect($rows)->keyBy('user_id');

        return $participants
            ->map(fn (InvestmentParticipant $participant) => [
                'user_id' => (int) $participant->user_id,
                'contribution' => (float) $participant->contribution_amount,
                'profit_share_cents' => (int) $sharesByUserId[(int) $participant->user_id]['profit_share_cents'],
            ])
            ->values()
            ->all();
    }

    /**
     * @return array{0: int, 1: string} Profit in cents and applied rate % for storage.
     */
    private function resolveMonthlyProfit(Investment $investment, float $principal, string $ratePct, Carbon $monthStart): array
    {
        if ($investment->usesTotalProfitPlan()) {
            $monthlyProfit = $investment->poolProfitForMonth($monthStart);
            $profitCents = (int) round($monthlyProfit * 100);
            $rate = $principal > 0 && $monthlyProfit > 0
                ? number_format(($monthlyProfit / $principal) * 100, 4, '.', '')
                : '0.0000';

            return [$profitCents, $rate];
        }

        $profitCents = (int) round($principal * ((float) $ratePct / 100) * 100);

        return [$profitCents, $ratePct];
    }
}
