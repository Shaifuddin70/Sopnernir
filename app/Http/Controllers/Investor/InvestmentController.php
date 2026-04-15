<?php

namespace App\Http\Controllers\Investor;

use App\Http\Controllers\Controller;
use App\Models\Investment;
use App\Models\InvestmentParticipant;
use App\Models\InvestmentPeriodUser;
use App\Support\PaginationPerPage;
use App\Support\SqlLike;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\View\View;

class InvestmentController extends Controller
{
    public function index(Request $request): View|JsonResponse
    {
        /** @var \App\Models\User $user */
        $user = Auth::user();
        $userId = $user->id;

        if ($request->ajax()) {
            return match ($request->query('ajax_fragment')) {
                'pools' => response()->json([
                    'html' => view('investor.investments.partials.pools-fragment', [
                        'investments' => $this->paginatedPortfolioPools($request, $userId),
                        'portfolioProfitTotal' => $this->portfolioProfitTotalForUser($userId),
                    ])->render(),
                ]),
                'profit' => response()->json([
                    'html' => view('investor.investments.partials.profit-month-fragment', [
                        'profitByMonth' => $this->paginatedProfitByMonth($request, $userId),
                    ])->render(),
                ]),
                default => abort(404),
            };
        }

        $investments = $this->paginatedPortfolioPools($request, $userId);
        $profitByMonth = $this->paginatedProfitByMonth($request, $userId);
        $portfolioProfitTotal = $this->portfolioProfitTotalForUser($userId);
        $totalTaggedCapital = $this->totalTaggedCapitalForUser($userId);
        $returnOnTaggedCapitalPct = $totalTaggedCapital > 0
            ? number_format(($portfolioProfitTotal / $totalTaggedCapital) * 100, 2, '.', '')
            : null;

        return view('investor.investments.index', compact(
            'investments',
            'profitByMonth',
            'portfolioProfitTotal',
            'totalTaggedCapital',
            'returnOnTaggedCapitalPct',
        ));
    }

    public function show(Request $request, Investment $investment): View|JsonResponse
    {
        $this->authorize('view', $investment);

        /** @var \App\Models\User $user */
        $user = Auth::user();
        $userId = $user->id;

        if ($request->ajax() && $request->query('ajax_fragment') === 'pool_profit') {
            return response()->json([
                'html' => view('investor.investments.partials.show-profit-fragment', [
                    'investment' => $investment,
                    'myProfitByMonth' => $this->paginatedPoolProfitForUser($request, $investment, $userId),
                ])->render(),
            ]);
        }

        $investment->load([
            'documents',
            'participants.user',
        ]);

        $myParticipant = $investment->participants->firstWhere('user_id', $userId);

        $myTotalProfit = (string) InvestmentPeriodUser::query()
            ->where('user_id', $userId)
            ->whereHas('period', fn ($q) => $q->where('investment_id', $investment->id))
            ->sum('profit_share');

        $myProfitByMonth = $this->paginatedPoolProfitForUser($request, $investment, $userId);

        return view('investor.investments.show', compact('investment', 'myParticipant', 'myTotalProfit', 'myProfitByMonth'));
    }

    private function paginatedPortfolioPools(Request $request, int $userId): LengthAwarePaginator
    {
        $poolLike = SqlLike::term($request->query('search'));

        return Investment::query()
            ->where('is_active', true)
            ->whereHas('participants', fn ($q) => $q->where('user_id', $userId))
            ->when($poolLike, fn ($q) => $q->where('title', 'like', $poolLike))
            ->withSum(['participants as my_contribution' => fn ($q) => $q->where('user_id', $userId)], 'contribution_amount')
            ->withSum(['periodUserShares as my_profit' => function ($q) use ($userId): void {
                $q->where('investment_period_users.user_id', $userId)
                    ->whereHas('period', fn ($p) => $p->whereHas('investment', fn ($inv) => $inv->where('is_active', true)));
            }], 'profit_share')
            ->latest()
            ->paginate(PaginationPerPage::resolve($request, 'pools_per_page', 15))
            ->withQueryString();
    }

    private function paginatedProfitByMonth(Request $request, int $userId): LengthAwarePaginator
    {
        $profitLike = SqlLike::term($request->query('profit_search'));

        return InvestmentPeriodUser::query()
            ->where('user_id', $userId)
            ->join('investment_periods', 'investment_period_users.investment_period_id', '=', 'investment_periods.id')
            ->join('investments', 'investments.id', '=', 'investment_periods.investment_id')
            ->where('investments.is_active', true)
            ->when($profitLike, function ($q) use ($profitLike): void {
                $q->where(function ($w) use ($profitLike): void {
                    $w->where('investments.title', 'like', $profitLike)
                        ->orWhere('investment_periods.month', 'like', $profitLike)
                        ->orWhere('investment_period_users.profit_share', 'like', $profitLike);
                });
            })
            ->orderByDesc('investment_periods.month')
            ->select('investment_period_users.*')
            ->with(['period.investment'])
            ->paginate(PaginationPerPage::resolve($request, 'profit_per_page', 10), ['investment_period_users.*'], 'profit_page')
            ->withQueryString();
    }

    private function paginatedPoolProfitForUser(Request $request, Investment $investment, int $userId): LengthAwarePaginator
    {
        $monthLike = SqlLike::term($request->query('search'));

        return InvestmentPeriodUser::query()
            ->where('user_id', $userId)
            ->whereHas('period', fn ($q) => $q->where('investment_id', $investment->id))
            ->join('investment_periods', 'investment_period_users.investment_period_id', '=', 'investment_periods.id')
            ->when($monthLike, function ($q) use ($monthLike): void {
                $q->where(function ($w) use ($monthLike): void {
                    $w->where('investment_periods.month', 'like', $monthLike)
                        ->orWhere('investment_period_users.profit_share', 'like', $monthLike);
                });
            })
            ->orderByDesc('investment_periods.month')
            ->select('investment_period_users.*')
            ->with('period')
            ->paginate(PaginationPerPage::resolve($request, 'pool_profit_per_page', 10), ['investment_period_users.*'], 'profit_page')
            ->withQueryString();
    }

    private function portfolioProfitTotalForUser(int $userId): float
    {
        return (float) InvestmentPeriodUser::query()
            ->where('user_id', $userId)
            ->whereHas('period', fn ($q) => $q->whereHas('investment', fn ($inv) => $inv->where('is_active', true)))
            ->sum('profit_share');
    }

    private function totalTaggedCapitalForUser(int $userId): float
    {
        return (float) InvestmentParticipant::query()
            ->where('user_id', $userId)
            ->whereHas('investment', fn ($q) => $q->where('is_active', true))
            ->sum('contribution_amount');
    }
}
