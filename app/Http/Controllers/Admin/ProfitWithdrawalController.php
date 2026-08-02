<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Investment;
use App\Models\InvestmentProfitWithdrawalBatch;
use App\Services\ProfitWithdrawalService;
use App\Support\PaginationPerPage;
use App\Support\SqlLike;
use Carbon\Carbon;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\ValidationException;
use Illuminate\View\View;
use InvalidArgumentException;

class ProfitWithdrawalController extends Controller
{
    public function index(Request $request): View|JsonResponse
    {
        $this->authorize('create', Investment::class);

        $like = SqlLike::term($request->query('search'));

        $pools = Investment::query()
            ->whereHas('profitWithdrawals')
            ->withSum('profitWithdrawals as total_withdrawn', 'amount')
            ->withCount('profitWithdrawals as withdrawals_count')
            ->withMax('profitWithdrawals as last_withdrawn_at', 'withdrawn_at')
            ->when($like, function ($q) use ($like): void {
                $q->where(function ($w) use ($like): void {
                    $w->where('title', 'like', $like)
                        ->orWhere('deed_no', 'like', $like)
                        ->orWhere('notes', 'like', $like);
                });
            })
            ->orderByDesc('total_withdrawn')
            ->orderBy('title')
            ->paginate(PaginationPerPage::resolve($request, 'per_page', 15))
            ->withQueryString();

        if ($request->ajax()) {
            return response()->json([
                'html' => view('admin.profit-withdrawals.partials.table-fragment', compact('pools'))->render(),
            ]);
        }

        $summaryTotalAmount = (float) InvestmentProfitWithdrawalBatch::query()->sum('amount');
        $summaryPoolCount = (int) Investment::query()->whereHas('profitWithdrawals')->count();
        $summaryCount = (int) InvestmentProfitWithdrawalBatch::query()->count();

        $recentBatches = InvestmentProfitWithdrawalBatch::query()
            ->with([
                'withdrawnBy:id,name',
                'withdrawals.investment:id,title,deed_no',
            ])
            ->latest('withdrawn_at')
            ->latest('id')
            ->limit(10)
            ->get();

        return view('admin.profit-withdrawals.index', compact(
            'pools',
            'recentBatches',
            'summaryTotalAmount',
            'summaryPoolCount',
            'summaryCount',
        ));
    }

    public function show(InvestmentProfitWithdrawalBatch $batch): View
    {
        $this->authorize('create', Investment::class);

        $batch->load([
            'withdrawnBy:id,name,email',
            'withdrawals.investment:id,title,deed_no',
        ]);

        return view('admin.profit-withdrawals.show', compact('batch'));
    }

    public function preview(Request $request, ProfitWithdrawalService $withdrawals): JsonResponse
    {
        $this->authorize('create', Investment::class);

        $validated = $request->validate([
            'investment_ids' => ['required', 'array', 'min:1'],
            'investment_ids.*' => ['integer', 'exists:investments,id'],
            'through_date' => ['required', 'date', 'before_or_equal:today'],
        ]);

        $throughDate = Carbon::parse($validated['through_date'])->startOfDay();

        try {
            $rows = $withdrawals->previewMany($validated['investment_ids'], $throughDate);
        } catch (InvalidArgumentException $e) {
            throw ValidationException::withMessages(['through_date' => $e->getMessage()]);
        }

        $totalAvailable = collect($rows)->sum(fn (array $row) => (float) $row['available']);

        return response()->json([
            'rows' => $rows,
            'total_available' => number_format($totalAvailable, 2, '.', ''),
            'withdrawable_count' => collect($rows)->where('can_withdraw', true)->count(),
        ]);
    }

    public function store(Request $request, Investment $investment, ProfitWithdrawalService $withdrawals): RedirectResponse|JsonResponse
    {
        $this->authorize('withdrawProfits', $investment);

        $validated = $request->validate([
            'through_date' => ['required', 'date', 'before_or_equal:today'],
            'notes' => ['nullable', 'string', 'max:2000'],
        ]);

        $throughDate = Carbon::parse($validated['through_date'])->startOfDay();

        try {
            $batch = $withdrawals->withdraw(
                $investment,
                $throughDate,
                $request->user(),
                $validated['notes'] ?? null,
            );
        } catch (InvalidArgumentException $e) {
            throw ValidationException::withMessages(['through_date' => $e->getMessage()]);
        }

        $message = __('Withdrew :amount through :date from :title.', [
            'amount' => number_format((float) $batch->amount, 2, '.', ''),
            'date' => $throughDate->translatedFormat('j M Y'),
            'title' => $investment->title,
        ]);

        if ($request->ajax() || $request->wantsJson()) {
            return response()->json([
                'ok' => true,
                'message' => $message,
                'batch_id' => $batch->id,
                'amount' => (string) $batch->amount,
            ]);
        }

        return back()->with('status', $message);
    }

    public function bulkStore(Request $request, ProfitWithdrawalService $withdrawals): RedirectResponse|JsonResponse
    {
        $this->authorize('create', Investment::class);

        $validated = $request->validate([
            'investment_ids' => ['required', 'array', 'min:1'],
            'investment_ids.*' => ['integer', 'exists:investments,id'],
            'through_date' => ['required', 'date', 'before_or_equal:today'],
            'notes' => ['nullable', 'string', 'max:2000'],
        ]);

        $investments = Investment::query()
            ->whereIn('id', $validated['investment_ids'])
            ->get();

        foreach ($investments as $investment) {
            $this->authorize('withdrawProfits', $investment);
        }

        $throughDate = Carbon::parse($validated['through_date'])->startOfDay();

        try {
            $batch = $withdrawals->withdrawMany(
                $validated['investment_ids'],
                $throughDate,
                $request->user(),
                $validated['notes'] ?? null,
            );
        } catch (InvalidArgumentException $e) {
            throw ValidationException::withMessages(['through_date' => $e->getMessage()]);
        }

        $message = __('Withdrew :amount across :count investment(s) through :date.', [
            'amount' => number_format((float) $batch->amount, 2, '.', ''),
            'count' => $batch->pools_count,
            'date' => $throughDate->translatedFormat('j M Y'),
        ]);

        if ($request->ajax() || $request->wantsJson()) {
            return response()->json([
                'ok' => true,
                'message' => $message,
                'batch_id' => $batch->id,
                'count' => $batch->pools_count,
                'total_amount' => (string) $batch->amount,
            ]);
        }

        return back()->with('status', $message);
    }
}
