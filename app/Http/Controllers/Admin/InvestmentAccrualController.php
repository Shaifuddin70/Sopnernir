<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Investment;
use App\Services\InvestmentAccrualService;
use Carbon\Carbon;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use InvalidArgumentException;

class InvestmentAccrualController extends Controller
{
    public function __construct(
        private InvestmentAccrualService $accrualService
    ) {}

    public function store(Request $request, Investment $investment): RedirectResponse
    {
        $this->authorize('accrue', $investment);

        $investment->refresh();

        if (! $investment->is_active) {
            return back()->withErrors([
                'accrual' => __('This investment is inactive. Activate it before recording accruals.'),
            ]);
        }

        $validated = $request->validate([
            'month' => ['nullable', 'regex:/^\d{4}-\d{2}$/'],
            'applied_rate_pct' => ['nullable', 'numeric', 'min:0', 'max:100'],
        ]);

        $month = ! empty($validated['month'])
            ? Carbon::createFromFormat('Y-m', $validated['month'])->startOfMonth()
            : now()->startOfMonth();

        $rateOverride = $request->filled('applied_rate_pct')
            ? (string) $validated['applied_rate_pct']
            : null;

        try {
            $this->accrualService->accrue($investment, $month, $rateOverride);
        } catch (InvalidArgumentException $e) {
            return back()->withErrors(['accrual' => $e->getMessage()]);
        }

        return back()->with('status', __('Monthly accrual recorded.'));
    }

    /**
     * Backfill every missing calendar month from the pool’s first accrual month through the
     * current month (same logic as `php artisan investments:accrue-monthly` for this pool).
     */
    public function fillMissing(Investment $investment): RedirectResponse
    {
        $this->authorize('accrue', $investment);

        $investment->refresh();

        if ($investment->status !== Investment::STATUS_ACTIVE) {
            return back()->withErrors([
                'accrual' => __('Only active pools can receive automatic month fills.'),
            ]);
        }

        if (! $investment->is_active) {
            return back()->withErrors([
                'accrual' => __('This investment is inactive. Activate it before filling accrual months.'),
            ]);
        }

        if ($investment->participants()->doesntExist()) {
            return back()->withErrors([
                'accrual' => __('Add at least one tagged investor with a contribution before filling months.'),
            ]);
        }

        $through = Investment::accrualThroughInclusive();
        $first = $investment->firstAccrualMonthStart()->copy()->startOfMonth();

        if ($first->gt($through)) {
            return back()->withErrors([
                'accrual' => __(
                    'This pool’s first accrual month (:first) is after the latest month we can fill (:through). Move “first accrual month” earlier or wait until the calendar catches up.',
                    ['first' => $first->translatedFormat('F Y'), 'through' => $through->translatedFormat('F Y')]
                ),
            ]);
        }

        $result = $this->accrualService->syncMonthsThrough($investment, $through);
        $added = $result['created'];
        $recalculated = $result['recalculated'];

        if ($added === 0 && $recalculated === 0) {
            return back()->with(
                'status',
                __('No new accrual months were added; this pool already has rows through :month.', ['month' => $through->translatedFormat('F Y')])
            );
        }

        $parts = [];
        if ($added > 0) {
            $parts[] = __('Recorded :count missing accrual month(s) through :month.', ['count' => $added, 'month' => $through->translatedFormat('F Y')]);
        }
        if ($recalculated > 0) {
            $parts[] = __('Recalculated :count posted accrual month(s).', ['count' => $recalculated]);
        }

        return back()->with('status', implode(' ', $parts));
    }
}
