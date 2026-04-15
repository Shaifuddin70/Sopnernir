<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Investment;
use App\Models\User;
use App\Support\PaginationPerPage;
use App\Support\SqlLike;
use App\Services\InvestmentAccrualService;
use Carbon\Carbon;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
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
            ->when($like, function ($q) use ($like): void {
                $q->where(function ($q) use ($like): void {
                    $q->where('title', 'like', $like)
                        ->orWhere('notes', 'like', $like)
                        ->orWhere('status', 'like', $like);
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

        return view('admin.investments.index', compact('investments'));
    }

    public function create(): View
    {
        return view('admin.investments.create');
    }

    public function store(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'title' => ['required', 'string', 'max:255'],
            'notes' => ['nullable', 'string'],
            'period_start' => ['nullable', 'regex:/^\d{4}-\d{2}$/'],
            'default_monthly_rate_pct' => ['required', 'numeric', 'min:0', 'max:100'],
            'status' => ['required', 'in:draft,active,closed'],
        ]);

        $validated['period_start'] = ! empty($validated['period_start'])
            ? Carbon::createFromFormat('Y-m', $validated['period_start'])->startOfMonth()->toDateString()
            : null;
        $validated['created_by'] = $request->user()->id;
        $validated['is_active'] = $request->boolean('is_active', true);

        $investment = Investment::create($validated);

        return redirect()->route('admin.investments.show', $investment)
            ->with('status', 'Investment created.');
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
        $investment->loadSum('participants', 'contribution_amount');
        $investment->loadSum('periods', 'profit_amount');

        $periods = $this->paginatedPeriodsForShow($request, $investment);
        $participants = $this->paginatedParticipantsForShow($request, $investment);

        $investorUsers = User::query()
            ->where('is_active', true)
            ->orderBy('name')
            ->get();

        return view('admin.investments.show', compact('investment', 'investorUsers', 'periods', 'participants'));
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

    public function edit(Investment $investment): View
    {
        return view('admin.investments.edit', compact('investment'));
    }

    public function update(Request $request, Investment $investment): RedirectResponse
    {
        $validated = $request->validate([
            'title' => ['required', 'string', 'max:255'],
            'notes' => ['nullable', 'string'],
            'period_start' => ['nullable', 'regex:/^\d{4}-\d{2}$/'],
            'default_monthly_rate_pct' => ['required', 'numeric', 'min:0', 'max:100'],
            'status' => ['required', 'in:draft,active,closed'],
        ]);

        $validated['period_start'] = ! empty($validated['period_start'])
            ? Carbon::createFromFormat('Y-m', $validated['period_start'])->startOfMonth()->toDateString()
            : null;
        $validated['is_active'] = $request->exists('is_active')
            ? $request->boolean('is_active')
            : $investment->is_active;

        $investment->update($validated);

        // Keep monthly accrual rows aligned with pool settings (e.g. first accrual month).
        // Runs here even when INVESTMENT_AUTO_ACCRUE_ON_SAVE is false, because saving this
        // form is an explicit admin action; model listeners still respect that env flag.
        $investment->refresh();
        $created = 0;
        $recalculated = 0;
        if ($investment->status === Investment::STATUS_ACTIVE && $investment->is_active && $investment->participants()->exists()) {
            $syncResult = app(InvestmentAccrualService::class)->syncMonthsThrough(
                $investment,
                Investment::accrualThroughInclusive()
            );
            $created = $syncResult['created'];
            $recalculated = $syncResult['recalculated'];
        }

        $statusParts = [__('Investment updated.')];
        if ($created > 0) {
            $statusParts[] = __('Recorded :count missing accrual month(s).', ['count' => $created]);
        }
        if ($recalculated > 0) {
            $statusParts[] = __('Recalculated :count posted accrual month(s).', ['count' => $recalculated]);
        }

        return redirect()->route('admin.investments.show', $investment)
            ->with('status', implode(' ', $statusParts));
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
