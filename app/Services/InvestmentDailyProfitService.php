<?php

namespace App\Services;

use App\Models\Investment;
use App\Models\InvestmentParticipant;
use App\Models\User;
use App\Support\PaginationPerPage;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Pagination\LengthAwarePaginator;
use Illuminate\Support\Collection;

class InvestmentDailyProfitService
{
    /**
     * @return array{
     *     total_capital: string,
     *     total_projected_profit: string,
     *     profit_til_today: string,
     *     total_til_today: string,
     *     investment_count: int,
     *     as_of_date: string,
     * }
     */
    public function portfolioSummaryForUser(User $user, ?Carbon $asOf = null): array
    {
        $rows = $this->portfolioRowsForUser($user, $asOf);
        $asOf = ($asOf ?? Carbon::today())->copy()->startOfDay();

        $capital = $rows->sum(fn (array $row) => (float) $row['capital']);
        $projected = $rows->sum(fn (array $row) => (float) $row['projected_profit']);
        $tilToday = $rows->sum(fn (array $row) => (float) $row['profit_til_today']);

        return [
            'total_capital' => $this->decimal($capital),
            'total_projected_profit' => $this->decimal($projected),
            'profit_til_today' => $this->decimal($tilToday),
            'total_til_today' => $this->decimal($capital + $tilToday),
            'investment_count' => $rows->count(),
            'as_of_date' => $asOf->toDateString(),
        ];
    }

    /**
     * @return array{
     *     total_capital: string,
     *     total_projected_profit: string,
     *     profit_til_today: string,
     *     total_til_today: string,
     *     member_count: int,
     *     per_person_profit_til_today: string,
     *     as_of_date: string,
     * }
     */
    public function platformSummary(?Carbon $asOf = null): array
    {
        $asOf = ($asOf ?? Carbon::today())->copy()->startOfDay();

        $investments = Investment::query()
            ->where('is_active', true)
            ->where('status', Investment::STATUS_ACTIVE)
            ->whereNotNull('deed_completion_deadline')
            ->with('participants')
            ->get();

        $capital = 0.0;
        $projected = 0.0;
        $tilToday = 0.0;
        $memberIds = [];

        foreach ($investments as $investment) {
            $pool = $this->poolProjection($investment, $asOf);
            if ($pool === null) {
                continue;
            }

            $capital += (float) $investment->participants->sum('contribution_amount');
            $projected += $pool['projected_profit'];
            $tilToday += $pool['profit_til_today'];

            foreach ($investment->participants as $participant) {
                $memberIds[$participant->user_id] = true;
            }
        }

        $memberCount = count($memberIds);
        $perPerson = $memberCount > 0 ? $tilToday / $memberCount : 0.0;

        return [
            'total_capital' => $this->decimal($capital),
            'total_projected_profit' => $this->decimal($projected),
            'profit_til_today' => $this->decimal($tilToday),
            'total_til_today' => $this->decimal($capital + $tilToday),
            'member_count' => $memberCount,
            'per_person_profit_til_today' => $this->decimal($perPerson),
            'as_of_date' => $asOf->toDateString(),
        ];
    }

    /**
     * @return Collection<int, array{
     *     investment_id: int,
     *     title: string,
     *     deed_no: string,
     *     start_date: string,
     *     end_date: string,
     *     capital: string,
     *     total_amount: string,
     *     projected_profit: string,
     *     profit_amount: string,
     *     profit_til_today: string,
     *     daily_profit: string,
     *     plan_days: int,
     *     days_elapsed: int,
     * }>
     */
    public function portfolioRowsForUser(User $user, ?Carbon $asOf = null): Collection
    {
        $asOf = ($asOf ?? Carbon::today())->copy()->startOfDay();

        $participants = InvestmentParticipant::query()
            ->where('user_id', $user->id)
            ->whereHas('investment', fn ($q) => $q->where('is_active', true))
            ->with('investment.participants')
            ->get();

        return $participants
            ->map(function (InvestmentParticipant $participant) use ($asOf): ?array {
                $investment = $participant->investment;
                if ($investment === null) {
                    return null;
                }

                $pool = $this->poolProjection($investment, $asOf);
                if ($pool === null) {
                    return null;
                }

                $userCapital = (float) $participant->contribution_amount;
                $poolCapital = (float) $investment->participants->sum('contribution_amount');
                $share = $poolCapital > 0 ? $userCapital / $poolCapital : 0.0;

                $userProjected = $pool['projected_profit'] * $share;
                $userTilToday = $pool['profit_til_today'] * $share;
                $userDaily = $pool['daily_profit'] * $share;

                $poolTotalInvested = $investment->total_invested_amount !== null && (float) $investment->total_invested_amount > 0
                    ? (float) $investment->total_invested_amount
                    : $poolCapital;
                $poolTotalProfit = $investment->usesTotalProfitPlan()
                    ? (float) $investment->total_profit_amount
                    : (float) $pool['projected_profit'];

                return [
                    'investment_id' => $investment->id,
                    'title' => $investment->title,
                    'deed_no' => (string) ($investment->deed_no ?? $investment->id),
                    'start_date' => $pool['start_date'],
                    'end_date' => $pool['end_date'],
                    'capital' => $this->decimal($userCapital),
                    'total_amount' => $this->decimal($userCapital),
                    'pool_total_amount' => $this->decimal($poolTotalInvested),
                    'projected_profit' => $this->decimal($userProjected),
                    'profit_amount' => $this->decimal($userProjected),
                    'pool_profit_amount' => $this->decimal($poolTotalProfit),
                    'profit_til_today' => $this->decimal($userTilToday),
                    'daily_profit' => $this->decimal($userDaily),
                    'plan_days' => $pool['plan_days'],
                    'days_elapsed' => $pool['days_elapsed'],
                ];
            })
            ->filter()
            ->sortByDesc('investment_id')
            ->values();
    }

    public function paginatedPortfolioRowsForUser(User $user, Request $request, int $defaultPerPage = 10): LengthAwarePaginator
    {
        $rows = $this->portfolioRowsForUser($user);
        $perPage = PaginationPerPage::resolve($request, 'per_page', $defaultPerPage);
        $page = max(1, (int) $request->query('page', 1));

        return new LengthAwarePaginator(
            $rows->forPage($page, $perPage)->values(),
            $rows->count(),
            $perPage,
            $page,
            [
                'path' => $request->url(),
                'query' => $request->except('page'),
            ]
        );
    }

    /**
     * @return array{
     *     start_date: string,
     *     end_date: string,
     *     projected_profit: float,
     *     profit_til_today: float,
     *     daily_profit: float,
     *     plan_days: int,
     *     days_elapsed: int,
     * }|null
     */
    public function poolProjection(Investment $investment, ?Carbon $asOf = null): ?array
    {
        $asOf = ($asOf ?? Carbon::today())->copy()->startOfDay();
        $end = $investment->planCompletionDate();

        if ($end === null) {
            return null;
        }

        $start = $investment->planStartDay();
        if ($start->gt($end)) {
            return null;
        }

        $planDays = $investment->planDayCount();
        $daysElapsed = $investment->profitDaysElapsedThrough($asOf);
        $projectedProfit = $this->projectedPoolProfit($investment);
        $dailyProfit = $planDays > 0 ? $projectedProfit / $planDays : 0.0;
        $profitTilToday = $investment->usesTotalProfitPlan()
            ? $investment->cumulativeProfitThroughDate($asOf)
            : min($projectedProfit, $dailyProfit * $daysElapsed);

        return [
            'start_date' => $start->toDateString(),
            'end_date' => $end->toDateString(),
            'projected_profit' => $projectedProfit,
            'profit_til_today' => $profitTilToday,
            'daily_profit' => $dailyProfit,
            'plan_days' => $planDays,
            'days_elapsed' => $daysElapsed,
        ];
    }

    public function planStartDate(Investment $investment): Carbon
    {
        return $investment->planStartDay();
    }

    public function projectedPoolProfit(Investment $investment): float
    {
        if ($investment->usesTotalProfitPlan()) {
            return round((float) $investment->total_profit_amount, 2);
        }

        $investment->loadMissing('participants');

        $principal = (float) $investment->participants->sum('contribution_amount');
        if ($principal <= 0) {
            return 0.0;
        }

        $rate = (float) $investment->default_monthly_rate_pct;
        $startMonth = $investment->firstAccrualMonthStart()->copy()->startOfMonth();
        $endMonth = $investment->planCompletionMonthStart();

        if ($endMonth === null || $startMonth->gt($endMonth)) {
            return 0.0;
        }

        $total = 0.0;
        for ($month = $startMonth->copy(); $month->lte($endMonth); $month->addMonth()) {
            $total += $principal * ($rate / 100);
        }

        return round($total, 2);
    }

    private function decimal(float $value): string
    {
        return number_format($value, 2, '.', '');
    }
}
