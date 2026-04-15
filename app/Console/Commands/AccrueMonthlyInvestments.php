<?php

namespace App\Console\Commands;

use App\Models\Investment;
use App\Models\InvestmentPeriod;
use App\Services\InvestmentAccrualService;
use Carbon\Carbon;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Log;
use InvalidArgumentException;

class AccrueMonthlyInvestments extends Command
{
    protected $signature = 'investments:accrue-monthly
                            {--through= : Last calendar month to accrue (YYYY-MM). Default: current month.}
                            {--month= : Accrue only this single month (YYYY-MM) for each pool (clamped to each pool first accrual month).}
                            {--explain : Print each active pool (participants, existing months, skips) even when nothing new is recorded.}';

    protected $description = 'Record missing monthly accruals for every active investment (default rate), from each pool’s first accrual month through the current calendar month.';

    public function handle(InvestmentAccrualService $accrualService): int
    {
        $singleMonth = $this->option('month')
            ? Carbon::createFromFormat('Y-m', (string) $this->option('month'))->startOfMonth()
            : null;

        $through = $singleMonth
            ?? ($this->option('through')
                ? Carbon::createFromFormat('Y-m', (string) $this->option('through'))->startOfMonth()
                : Investment::accrualThroughInclusive());

        $recorded = 0;
        $explain = $this->option('explain') || $this->output->isVerbose();

        $onSkippedMonth = $explain
            ? function (Investment $investment, Carbon $month, InvalidArgumentException $e): void {
                $this->warn("  [{$investment->id}] {$investment->title} {$month->format('Y-m')}: {$e->getMessage()}");
            }
            : null;

        $activeInvestments = Investment::query()
            ->where('status', Investment::STATUS_ACTIVE)
            ->where('is_active', true)
            ->orderBy('id');

        if ($explain && ! $singleMonth) {
            $count = (clone $activeInvestments)->count();
            $this->line("Active pools: {$count}. Fill range ends {$through->format('Y-m')} (inclusive).");
        }

        foreach ($activeInvestments->cursor() as $investment) {
            $poolStart = $investment->firstAccrualMonthStart()->copy()->startOfMonth();

            if ($singleMonth) {
                if ($singleMonth->lt($poolStart)) {
                    $this->warn("Skipped [{$investment->id}] {$investment->title}: month {$singleMonth->format('Y-m')} is before first accrual month {$poolStart->format('Y-m')}.");

                    continue;
                }
                $recorded += $this->tryAccrueMonth($accrualService, $investment, $singleMonth->copy());
            } else {
                if ($explain) {
                    $participantCount = $investment->participants()->count();
                    $principal = (float) $investment->participants()->sum('contribution_amount');
                    $existingMonths = $investment->periods()->orderBy('month')->pluck('month')
                        ->map(fn ($d) => Carbon::parse($d)->format('Y-m'))->values()->all();
                    $existingLabel = $existingMonths === [] ? '(none)' : implode(', ', $existingMonths);

                    if ($poolStart->gt($through)) {
                        $this->warn("[{$investment->id}] {$investment->title}: first accrual month {$poolStart->format('Y-m')} is after fill-through {$through->format('Y-m')} — nothing to record.");
                    } else {
                        $this->line("[{$investment->id}] {$investment->title}: first={$poolStart->format('Y-m')} participants={$participantCount} principal_sum={$principal} existing_months={$existingLabel}");
                    }
                }

                $before = $recorded;
                $recorded += $accrualService->fillMissingMonthsThrough($investment, $through, $onSkippedMonth);
                if ($explain && ! $singleMonth && $poolStart->lte($through)) {
                    $added = $recorded - $before;
                    $this->line("  → recorded {$added} new month(s) for this pool.");
                }
            }
        }

        $rangeLabel = $singleMonth
            ? $singleMonth->format('Y-m')
            : "through {$through->format('Y-m')}";

        $this->info("Finished ({$rangeLabel}): {$recorded} accrual month(s) recorded.");

        if ($recorded === 0 && ! $explain) {
            $this->comment('No new months were written. Typical causes: every month already exists, no active pools, no tagged participants / zero principal, or first accrual month is after the fill-through month. Run again with --explain (or -v) to see each pool.');
        }

        return self::SUCCESS;
    }

    private function tryAccrueMonth(InvestmentAccrualService $accrualService, Investment $investment, Carbon $monthStart): int
    {
        $monthStart = $monthStart->copy()->startOfMonth();

        if ($this->periodExists($investment->id, $monthStart)) {
            return 0;
        }

        try {
            $accrualService->accrue($investment, $monthStart, null);
            $this->line("Accrued [{$investment->id}] {$investment->title} for {$monthStart->format('Y-m')}.");

            return 1;
        } catch (InvalidArgumentException $e) {
            $this->warn("Skipped [{$investment->id}] {$investment->title} {$monthStart->format('Y-m')}: {$e->getMessage()}");
            Log::info('Investment accrual skipped', [
                'investment_id' => $investment->id,
                'month' => $monthStart->format('Y-m'),
                'message' => $e->getMessage(),
            ]);

            return 0;
        }
    }

    private function periodExists(int $investmentId, Carbon $monthStart): bool
    {
        return InvestmentPeriod::query()
            ->where('investment_id', $investmentId)
            ->whereDate('month', $monthStart)
            ->exists();
    }
}
