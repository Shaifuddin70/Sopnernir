<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Investment;
use App\Models\User;
use App\Support\PaginationPerPage;
use App\Support\SqlLike;
use App\Services\InvestmentAccrualService;
use App\Services\InvestmentDailyProfitService;
use App\Services\InvestmentPlanCalculationService;
use App\Services\MonthlyPaymentService;
use Carbon\Carbon;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;
use Illuminate\Validation\ValidationException;
use Illuminate\View\View;

class InvestmentController extends Controller
{
    public function __construct()
    {
        $this->authorizeResource(Investment::class, 'investment');
    }

    public function index(Request $request): View|JsonResponse
    {
        $like = SqlLike::term($request->query('search'));

        $investments = Investment::query()
            ->withCount('participants')
            ->withSum('profitWithdrawals as profit_withdrawn_total', 'amount')
            ->when($like, function ($q) use ($like): void {
                $q->where(function ($q) use ($like): void {
                    $q->where('title', 'like', $like)
                        ->orWhere('notes', 'like', $like)
                        ->orWhere('deed_no', 'like', $like)
                        ->orWhere('status', 'like', $like)
                        ->orWhere('deed_completion_deadline', 'like', $like);
                });
            })
            ->latest()
            ->paginate(PaginationPerPage::resolve($request, 'per_page', 15))
            ->withQueryString();

        if ($request->ajax()) {
            return response()->json([
                'html' => view('admin.investments.partials.table-fragment', compact('investments'))->render(),
            ]);
        }

        $summary = app(InvestmentDailyProfitService::class)->platformSummary();
        $summary['investments_total'] = (int) Investment::query()->count();
        $summary['investments_active'] = (int) Investment::query()
            ->where('status', Investment::STATUS_ACTIVE)
            ->where('is_active', true)
            ->count();

        $editingInvestment = $this->resolveEditingInvestment($request);

        if ($editingInvestment) {
            $this->authorize('update', $editingInvestment);
        }

        return view('admin.investments.index', compact('investments', 'editingInvestment', 'summary'));
    }

    public function create(): RedirectResponse
    {
        return redirect()->route('admin.investments.index', ['new' => 1]);
    }

    public function store(Request $request): RedirectResponse
    {
        $validated = $this->validateInvestment($request);

        $paymentMonthInput = $request->validate([
            'payment_month' => ['required', 'date_format:Y-m'],
        ])['payment_month'];

        $paymentMonth = Carbon::createFromFormat('Y-m', $paymentMonthInput)->startOfMonth();

        $validated['created_by'] = $request->user()->id;
        $validated['is_active'] = $request->boolean('is_active', true);
        $validated['tagged_payment_month'] = $paymentMonth->toDateString();

        $investment = Investment::create($validated);

        $tagged = app(MonthlyPaymentService::class)->tagPaidInvestorsForMonth($investment, $paymentMonth);

        app(InvestmentPlanCalculationService::class)->syncStoredTotals($investment->fresh());

        if ($tagged > 0 && $investment->status === Investment::STATUS_DRAFT) {
            $investment->update(['status' => Investment::STATUS_ACTIVE]);
        }

        $investment->refresh();
        if (
            $tagged > 0
            && $investment->status === Investment::STATUS_ACTIVE
            && $investment->is_active
        ) {
            app(InvestmentAccrualService::class)->syncMonthsThrough(
                $investment,
                $investment->lastAccrualMonthInclusive()
            );
        }

        $statusParts = [__('Investment created.')];
        if ($tagged > 0) {
            $statusParts[] = __('Tagged :count investor(s) who paid for :month.', [
                'count' => $tagged,
                'month' => $paymentMonth->translatedFormat('F Y'),
            ]);
        } else {
            $statusParts[] = __('No paid investors for :month — tag investors manually on the pool page.', [
                'month' => $paymentMonth->translatedFormat('F Y'),
            ]);
        }

        return redirect()->route('admin.investments.show', $investment)
            ->with('status', implode(' ', $statusParts));
    }

    public function show(Request $request, Investment $investment): View|JsonResponse
    {
        if ($request->ajax()) {
            return match ($request->query('ajax_fragment')) {
                'periods' => response()->json([
                    'html' => view('admin.investments.partials.periods-fragment', [
                        'investment' => $investment,
                        'periods' => $this->paginatedPeriodsForShow($request, $investment),
                    ])->render(),
                ]),
                'participants' => response()->json([
                    'html' => view('admin.investments.partials.participants-fragment', [
                        'investment' => $investment,
                        'participants' => $this->paginatedParticipantsForShow($request, $investment),
                    ])->render(),
                ]),
                default => abort(404),
            };
        }

        $investment->load('documents');
        $investment->loadCount('participants');
        $investment->loadSum('participants', 'contribution_amount');
        $investment->loadSum('periods', 'profit_amount');
        $investment->loadSum('profitWithdrawals as profit_withdrawn_total', 'amount');
        $profitWithdrawals = $investment->profitWithdrawals()
            ->with(['withdrawnBy:id,name'])
            ->paginate(10, ['*'], 'withdrawals_page')
            ->withQueryString();

        $periods = $this->paginatedPeriodsForShow($request, $investment);
        $participants = $this->paginatedParticipantsForShow($request, $investment);

        $taggedUserIds = $investment->participants()->pluck('user_id');
        $investorUsers = User::query()
            ->where('is_active', true)
            ->when($taggedUserIds->isNotEmpty(), fn ($q) => $q->whereNotIn('id', $taggedUserIds))
            ->orderBy('name')
            ->get();

        $editingInvestment = $this->resolveEditingInvestment($request, $investment);
        $poolDaily = app(InvestmentDailyProfitService::class)->poolProjection($investment);
        $availableProfit = (float) ($poolDaily['profit_til_today'] ?? 0);

        return view('admin.investments.show', compact(
            'investment',
            'investorUsers',
            'periods',
            'participants',
            'editingInvestment',
            'profitWithdrawals',
            'poolDaily',
            'availableProfit',
        ));
    }

    private function paginatedPeriodsForShow(Request $request, Investment $investment): LengthAwarePaginator
    {
        $periodsLike = SqlLike::term($request->query('periods_search'));

        return $investment->periods()
            ->when($periodsLike, function ($q) use ($periodsLike): void {
                $q->where(function ($w) use ($periodsLike): void {
                    $w->where('month', 'like', $periodsLike)
                        ->orWhere('profit_amount', 'like', $periodsLike)
                        ->orWhere('principal_snapshot', 'like', $periodsLike)
                        ->orWhere('applied_rate_pct', 'like', $periodsLike);
                });
            })
            ->orderByDesc('month')
            ->paginate(PaginationPerPage::resolve($request, 'periods_per_page', 10), ['*'], 'periods_page')
            ->withQueryString();
    }

    private function paginatedParticipantsForShow(Request $request, Investment $investment): LengthAwarePaginator
    {
        $participantsLike = SqlLike::term($request->query('participants_search'));

        return $investment->participants()
            ->with('user')
            ->when($participantsLike, function ($q) use ($participantsLike): void {
                $q->where(function ($w) use ($participantsLike): void {
                    $w->where('contribution_amount', 'like', $participantsLike)
                        ->orWhereHas('user', function ($uq) use ($participantsLike): void {
                            $uq->where('name', 'like', $participantsLike)
                                ->orWhere('email', 'like', $participantsLike)
                                ->orWhere('phone', 'like', $participantsLike);
                        });
                });
            })
            ->latest()
            ->paginate(PaginationPerPage::resolve($request, 'participants_per_page', 10), ['*'], 'participants_page')
            ->withQueryString();
    }

    public function edit(Request $request, Investment $investment): RedirectResponse
    {
        if ($request->query('return') === 'show') {
            return redirect()->route('admin.investments.show', [$investment, 'edit' => 1]);
        }

        return redirect()->route('admin.investments.index', ['edit' => $investment->id]);
    }

    public function update(Request $request, Investment $investment): RedirectResponse
    {
        try {
            $validated = $this->validateInvestment($request, $investment);
        } catch (ValidationException $e) {
            return $this->redirectBackToEditForm($request, $investment)
                ->withInput()
                ->withErrors($e->errors());
        }

        $validated['is_active'] = $request->exists('is_active')
            ? $request->boolean('is_active')
            : $investment->is_active;

        $previousContribution = $investment->contribution_per_investor !== null
            ? number_format((float) $investment->contribution_per_investor, 2, '.', '')
            : null;

        $investment->update($validated);

        $plan = app(InvestmentPlanCalculationService::class);
        $fresh = $investment->fresh();
        $syncedParticipants = 0;

        $nextContribution = $fresh->contribution_per_investor !== null
            ? number_format((float) $fresh->contribution_per_investor, 2, '.', '')
            : null;

        if ($nextContribution !== null && $nextContribution !== $previousContribution) {
            $syncedParticipants = $plan->syncParticipantContributionsFromPlan($fresh);
            $fresh = $fresh->fresh();
        }

        $plan->syncStoredTotals($fresh);

        // Keep monthly accrual rows aligned with pool settings (e.g. first accrual month).
        // Runs here even when INVESTMENT_AUTO_ACCRUE_ON_SAVE is false, because saving this
        // form is an explicit admin action; model listeners still respect that env flag.
        $investment->refresh();
        $created = 0;
        $recalculated = 0;
        if ($investment->status === Investment::STATUS_ACTIVE && $investment->is_active && $investment->participants()->exists()) {
            $syncResult = app(InvestmentAccrualService::class)->syncMonthsThrough(
                $investment,
                $investment->lastAccrualMonthInclusive()
            );
            $created = $syncResult['created'];
            $recalculated = $syncResult['recalculated'];
        }

        $statusParts = [__('Investment updated.')];
        if ($syncedParticipants > 0) {
            $statusParts[] = __('Updated contribution for :count tagged investor(s) to :amount.', [
                'count' => $syncedParticipants,
                'amount' => $nextContribution,
            ]);
        }
        if ($created > 0) {
            $statusParts[] = __('Recorded :count missing accrual month(s).', ['count' => $created]);
        }
        if ($recalculated > 0) {
            $statusParts[] = __('Recalculated :count posted accrual month(s).', ['count' => $recalculated]);
        }

        if ($request->input('_return') === 'index') {
            return redirect()->route('admin.investments.index')
                ->with('status', implode(' ', $statusParts));
        }

        return redirect()->route('admin.investments.show', $investment)
            ->with('status', implode(' ', $statusParts));
    }

    private function resolveEditingInvestment(Request $request, ?Investment $investment = null): ?Investment
    {
        $shouldOpen = $request->filled('edit')
            || (old('_form') === 'edit-investment' && $request->session()->has('errors'));

        if (! $shouldOpen) {
            return null;
        }

        if ($investment !== null) {
            $editId = (int) (old('_investment_id') ?? $investment->id);

            return $editId === $investment->id ? $investment : null;
        }

        $editId = old('_investment_id') ?? $request->query('edit');

        if (! $editId) {
            return null;
        }

        return Investment::query()->find($editId);
    }

    private function redirectBackToEditForm(Request $request, Investment $investment): RedirectResponse
    {
        if ($request->input('_return') === 'index') {
            return redirect()->route('admin.investments.index', ['edit' => $investment->id]);
        }

        return redirect()->route('admin.investments.show', [$investment, 'edit' => 1]);
    }

    /**
     * @return array<string, mixed>
     */
    private function validateInvestment(Request $request, ?Investment $investment = null): array
    {
        $validated = $request->validate([
            'title' => ['required', 'string', 'max:255'],
            'deed_no' => [
                'required',
                'string',
                'max:64',
                Rule::unique('investments', 'deed_no')->ignore($investment?->id),
            ],
            'notes' => ['nullable', 'string'],
            'period_start' => ['required', 'date'],
            'deed_completion_deadline' => ['required', 'date', 'after_or_equal:period_start'],
            'total_profit_amount' => ['required', 'numeric', 'min:0.01'],
            'contribution_per_investor' => ['required', 'numeric', 'min:0.01'],
            'status' => ['required', 'in:draft,active,closed'],
        ]);

        $validated['period_start'] = Carbon::parse($validated['period_start'])->toDateString();
        $validated['deed_completion_deadline'] = Carbon::parse($validated['deed_completion_deadline'])->toDateString();
        $validated['default_monthly_rate_pct'] = '0.0000';
        $validated['total_profit_amount'] = number_format((float) $validated['total_profit_amount'], 2, '.', '');
        $validated['contribution_per_investor'] = number_format((float) $validated['contribution_per_investor'], 2, '.', '');
        $validated['total_invested_amount'] = '0.00';

        $firstMonth = Carbon::parse($validated['period_start'])->startOfMonth();
        $completionMonth = Carbon::parse($validated['deed_completion_deadline'])->startOfMonth();
        if ($completionMonth->lt($firstMonth)) {
            throw ValidationException::withMessages([
                'deed_completion_deadline' => __('Ending date must be in or after the starting date month (:month).', [
                    'month' => $firstMonth->translatedFormat('F Y'),
                ]),
            ]);
        }

        return $validated;
    }

    public function toggleActive(Investment $investment): RedirectResponse
    {
        $this->authorize('update', $investment);

        $nowActive = ! $investment->is_active;
        $investment->update(['is_active' => $nowActive]);

        return back()->with(
            'status',
            $nowActive
                ? __('Investment is now active.')
                : __('Investment is now inactive.')
        );
    }
}
