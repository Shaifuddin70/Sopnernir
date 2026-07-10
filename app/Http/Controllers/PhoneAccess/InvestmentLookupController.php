<?php

namespace App\Http\Controllers\PhoneAccess;

use App\Http\Controllers\Controller;
use App\Models\Investment;
use App\Models\InvestmentParticipant;
use App\Models\InvestmentPeriodUser;
use App\Models\User;
use App\Services\DashboardMetricsService;
use App\Services\InvestmentDailyProfitService;
use Illuminate\Contracts\View\View;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;

class InvestmentLookupController extends Controller
{
    public function store(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'phone' => ['required', 'string', 'max:32'],
        ]);

        $user = User::query()
            ->where('phone', $validated['phone'])
            ->where('is_active', true)
            ->first();

        if (! $user) {
            return back()->withErrors([
                'phone' => __('No active user found with that phone number.'),
            ])->onlyInput('phone');
        }

        $request->session()->put('phone_access_user_id', $user->id);

        return redirect()->route('phone-access.investments.index');
    }

    public function destroy(Request $request): RedirectResponse
    {
        $request->session()->forget('phone_access_user_id');

        return redirect()->route('login')->with('status', __('Phone access session ended.'));
    }

    public function index(Request $request): View
    {
        $user = $this->phoneAccessUser($request);
        $userId = $user->id;

        $investments = Investment::query()
            ->where('is_active', true)
            ->whereHas('participants', fn ($q) => $q->where('user_id', $userId))
            ->withSum(['participants as my_contribution' => fn ($q) => $q->where('user_id', $userId)], 'contribution_amount')
            ->withSum(['periodUserShares as my_profit' => function ($q) use ($userId): void {
                $q->where('investment_period_users.user_id', $userId)
                    ->whereHas('period', fn ($p) => $p->whereHas('investment', fn ($inv) => $inv->where('is_active', true)));
            }], 'profit_share')
            ->latest()
            ->get();

        $totalInvestmentCount = $investments->count();

        $totalTaggedCapital = (float) InvestmentParticipant::query()
            ->where('user_id', $userId)
            ->whereHas('investment', fn ($q) => $q->where('is_active', true))
            ->sum('contribution_amount');

        $portfolioProfitTotal = (float) InvestmentPeriodUser::query()
            ->where('user_id', $userId)
            ->whereHas('period', fn ($q) => $q->whereHas('investment', fn ($inv) => $inv->where('is_active', true)))
            ->sum('profit_share');

        $totalAmount = $totalTaggedCapital + $portfolioProfitTotal;
        $platform = app(DashboardMetricsService::class)->platformSummary();
        $dailyProfit = app(InvestmentDailyProfitService::class);
        $dailyProfitSummary = $dailyProfit->portfolioSummaryForUser($user);
        $dailyProfitRows = $dailyProfit->portfolioRowsForUser($user);
        $dailyProfitRowsTotal = $dailyProfitRows->count();
        $platformDailySummary = $dailyProfit->platformSummary();

        return view('phone-access.investments.index', compact(
            'user',
            'investments',
            'totalInvestmentCount',
            'totalTaggedCapital',
            'portfolioProfitTotal',
            'totalAmount',
            'platform',
            'dailyProfitSummary',
            'dailyProfitRows',
            'dailyProfitRowsTotal',
            'platformDailySummary',
        ));
    }

    public function all(Request $request): View
    {
        $user = $this->phoneAccessUser($request);

        $investmentRows = app(InvestmentDailyProfitService::class)
            ->paginatedPortfolioRowsForUser($user, $request, 10);

        return view('phone-access.investments.all', compact('user', 'investmentRows'));
    }

    public function show(Request $request, Investment $investment): View
    {
        $user = $this->phoneAccessUser($request);
        $userId = $user->id;

        $isTagged = InvestmentParticipant::query()
            ->where('investment_id', $investment->id)
            ->where('user_id', $userId)
            ->exists();

        abort_unless($isTagged, 404);

        $myParticipant = InvestmentParticipant::query()
            ->where('investment_id', $investment->id)
            ->where('user_id', $userId)
            ->first();

        $myTotalProfit = (float) InvestmentPeriodUser::query()
            ->where('user_id', $userId)
            ->whereHas('period', fn ($q) => $q->where('investment_id', $investment->id))
            ->sum('profit_share');

        $myProfitByMonth = InvestmentPeriodUser::query()
            ->where('user_id', $userId)
            ->whereHas('period', fn ($q) => $q->where('investment_id', $investment->id))
            ->join('investment_periods', 'investment_period_users.investment_period_id', '=', 'investment_periods.id')
            ->orderByDesc('investment_periods.month')
            ->select('investment_period_users.*')
            ->with('period')
            ->get();

        $poolDaily = app(InvestmentDailyProfitService::class)->poolProjection($investment);
        $userCapital = (float) ($myParticipant?->contribution_amount ?? 0);
        $poolCapital = (float) InvestmentParticipant::query()
            ->where('investment_id', $investment->id)
            ->sum('contribution_amount');
        $share = $poolCapital > 0 ? $userCapital / $poolCapital : 0.0;
        $myDailyProfit = $poolDaily ? [
            'projected_profit' => number_format($poolDaily['projected_profit'] * $share, 2, '.', ''),
            'profit_til_today' => number_format($poolDaily['profit_til_today'] * $share, 2, '.', ''),
            'daily_profit' => number_format($poolDaily['daily_profit'] * $share, 2, '.', ''),
            'start_date' => $poolDaily['start_date'],
            'end_date' => $poolDaily['end_date'],
        ] : null;

        return view('phone-access.investments.show', compact(
            'user',
            'investment',
            'myParticipant',
            'myTotalProfit',
            'myProfitByMonth',
            'myDailyProfit',
        ));
    }

    private function phoneAccessUser(Request $request): User
    {
        $user = User::query()
            ->where('id', $request->session()->get('phone_access_user_id'))
            ->where('is_active', true)
            ->firstOrFail();

        return $user;
    }
}
