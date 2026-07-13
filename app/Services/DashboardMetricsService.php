<?php

namespace App\Services;

use App\Models\Investment;
use App\Models\InvestmentParticipant;
use App\Models\User;
use App\Support\PublicMediaUrl;
use App\Support\SqlLike;
use Carbon\Carbon;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Pagination\LengthAwarePaginator as Paginator;
use Illuminate\Support\Collection;

class DashboardMetricsService
{
    public function __construct(
        private InvestmentDailyProfitService $dailyProfit,
    ) {}

    /**
     * Platform money figures match InvestmentDailyProfitService (same as portfolio / phone access).
     *
     * @return array{
     *     investments_total: int,
     *     investments_active: int,
     *     total_contributions: string,
     *     total_projected_profit: string,
     *     profit_til_today: string,
     *     total_profit_distributed: string,
     *     total_amount: string,
     *     member_count: int,
     *     per_person_profit_til_today: string,
     *     as_of_date: string,
     * }
     */
    public function platformSummary(): array
    {
        $daily = $this->dailyProfit->platformSummary();

        return [
            'investments_total' => (int) Investment::query()->count(),
            'investments_active' => (int) Investment::query()
                ->where('status', Investment::STATUS_ACTIVE)
                ->where('is_active', true)
                ->count(),
            'total_contributions' => $daily['total_capital'],
            'total_projected_profit' => $daily['total_projected_profit'],
            'profit_til_today' => $daily['profit_til_today'],
            'total_profit_distributed' => $daily['profit_til_today'],
            'total_amount' => $daily['total_til_today'],
            'member_count' => $daily['member_count'],
            'per_person_profit_til_today' => $daily['per_person_profit_til_today'],
            'as_of_date' => $daily['as_of_date'],
        ];
    }

    /**
     * Personal portfolio figures match InvestmentDailyProfitService (same as My investments).
     *
     * @return array{
     *     investments_count: int,
     *     total_contribution: string,
     *     total_projected_profit: string,
     *     profit_til_today: string,
     *     total_profit: string,
     *     total_amount: string,
     *     return_on_tagged_capital_pct: string|null,
     *     as_of_date: string,
     * }
     */
    public function personalSummary(User $user): array
    {
        $daily = $this->dailyProfit->portfolioSummaryForUser($user);
        $contrib = (float) $daily['total_capital'];
        $profitTilToday = (float) $daily['profit_til_today'];

        return [
            'investments_count' => $daily['investment_count'],
            'total_contribution' => $daily['total_capital'],
            'total_projected_profit' => $daily['total_projected_profit'],
            'profit_til_today' => $daily['profit_til_today'],
            'total_profit' => $daily['profit_til_today'],
            'total_amount' => $daily['total_til_today'],
            'return_on_tagged_capital_pct' => $contrib > 0
                ? number_format(($profitTilToday / $contrib) * 100, 2, '.', '')
                : null,
            'as_of_date' => $daily['as_of_date'],
        ];
    }

    /**
     * Top investors ranked by profit til today (same daily projection as portfolio).
     *
     * @return LengthAwarePaginator<int, object{
     *     user_id: int,
     *     name: string,
     *     email: string,
     *     profile_image_url: string|null,
     *     total_contribution: string,
     *     total_profit: string,
     *     profit_til_today: string,
     *     total_projected_profit: string,
     *     total_til_today: string,
     *     pools_count: int,
     * }>
     */
    public function topInvestorsByProfitGlobally(int $perPage = 10, ?string $search = null): LengthAwarePaginator
    {
        return $this->paginateInvestorRankings(
            $this->dailyProfit->investorRankings(),
            $perPage,
            $search,
        );
    }

    /**
     * Top co-investors on pools the viewer participates in, ranked by profit til today.
     *
     * @return LengthAwarePaginator<int, object{
     *     user_id: int,
     *     name: string,
     *     email: string,
     *     profile_image_url: string|null,
     *     total_contribution: string,
     *     total_profit: string,
     *     profit_til_today: string,
     *     total_projected_profit: string,
     *     total_til_today: string,
     *     pools_count: int,
     * }>
     */
    public function topCoInvestorsByProfit(User $viewer, int $perPage = 10, ?string $search = null): LengthAwarePaginator
    {
        $investmentIds = InvestmentParticipant::query()
            ->where('user_id', $viewer->id)
            ->whereHas('investment', fn ($q) => $q->where('is_active', true))
            ->pluck('investment_id');

        if ($investmentIds->isEmpty()) {
            return $this->emptyTopInvestorsPaginator($perPage);
        }

        $allowedUserIds = InvestmentParticipant::query()
            ->whereIn('investment_id', $investmentIds)
            ->pluck('user_id')
            ->unique()
            ->all();

        $rankings = $this->dailyProfit->investorRankings()
            ->filter(fn (array $row) => in_array($row['user_id'], $allowedUserIds, true))
            ->values();

        return $this->paginateInvestorRankings($rankings, $perPage, $search);
    }

    /**
     * @return array{labels: list<string>, values: list<float>}
     */
    public function monthlyProfitTrendPlatform(int $months = 6): array
    {
        return $this->dailyProfit->monthlyPlatformProfitTrend($months);
    }

    /**
     * @return array{labels: list<string>, values: list<float>}
     */
    public function monthlyProfitTrendForUser(User $user, int $months = 6): array
    {
        $asOf = Carbon::today()->startOfDay();
        $end = $asOf->copy()->startOfMonth();
        $start = $end->copy()->subMonths($months - 1);

        $participants = InvestmentParticipant::query()
            ->where('user_id', $user->id)
            ->whereHas('investment', fn ($q) => $q
                ->where('is_active', true)
                ->where('status', Investment::STATUS_ACTIVE)
                ->whereNotNull('deed_completion_deadline'))
            ->with('investment.participants')
            ->get();

        $labels = [];
        $values = [];
        $cursor = $start->copy();

        while ($cursor->lte($end)) {
            $monthTotal = 0.0;

            foreach ($participants as $participant) {
                $investment = $participant->investment;
                if ($investment === null) {
                    continue;
                }

                $poolCapital = (float) $investment->participants->sum('contribution_amount');
                if ($poolCapital <= 0) {
                    continue;
                }

                $share = (float) $participant->contribution_amount / $poolCapital;
                $monthTotal += $this->dailyProfit->poolProfitForCalendarMonth($investment, $cursor, $asOf) * $share;
            }

            $labels[] = $cursor->translatedFormat('M Y');
            $values[] = round($monthTotal, 2);
            $cursor->addMonth();
        }

        return ['labels' => $labels, 'values' => $values];
    }

    /**
     * @return array{active: int, draft: int, closed: int}
     */
    public function investmentStatusBreakdownForUser(User $user): array
    {
        $investmentIds = InvestmentParticipant::query()
            ->where('user_id', $user->id)
            ->whereHas('investment', fn ($q) => $q->where('is_active', true))
            ->pluck('investment_id');

        if ($investmentIds->isEmpty()) {
            return ['active' => 0, 'draft' => 0, 'closed' => 0];
        }

        return [
            'active' => (int) Investment::query()
                ->whereIn('id', $investmentIds)
                ->where('status', Investment::STATUS_ACTIVE)
                ->count(),
            'draft' => (int) Investment::query()
                ->whereIn('id', $investmentIds)
                ->where('status', Investment::STATUS_DRAFT)
                ->count(),
            'closed' => (int) Investment::query()
                ->whereIn('id', $investmentIds)
                ->where('status', Investment::STATUS_CLOSED)
                ->count(),
        ];
    }

    /**
     * @return array{paid: int, unpaid: int}
     */
    public function currentMonthPaymentSummaryForUser(User $user): array
    {
        $rows = app(MonthlyPaymentService::class)->rowsForMonth(Carbon::now()->startOfMonth());
        $row = $rows->firstWhere('user_id', $user->id);
        $isPaid = $row !== null && $row['is_paid'];

        return [
            'paid' => $isPaid ? 1 : 0,
            'unpaid' => $isPaid ? 0 : 1,
        ];
    }

    /**
     * @return array{active: int, draft: int, closed: int}
     */
    public function investmentStatusBreakdown(): array
    {
        return [
            'active' => (int) Investment::query()
                ->where('is_active', true)
                ->where('status', Investment::STATUS_ACTIVE)
                ->count(),
            'draft' => (int) Investment::query()
                ->where('status', Investment::STATUS_DRAFT)
                ->count(),
            'closed' => (int) Investment::query()
                ->where('status', Investment::STATUS_CLOSED)
                ->count(),
        ];
    }

    /**
     * @return array{paid: int, unpaid: int}
     */
    public function currentMonthPaymentSummary(): array
    {
        $rows = app(MonthlyPaymentService::class)->rowsForMonth(Carbon::now()->startOfMonth());
        $paid = $rows->where('is_paid', true)->count();

        return [
            'paid' => $paid,
            'unpaid' => max(0, $rows->count() - $paid),
        ];
    }

    /**
     * @param  Collection<int, array{
     *     user_id: int,
     *     capital: float,
     *     projected_profit: float,
     *     profit_til_today: float,
     *     total_til_today: float,
     *     pools_count: int,
     * }>  $rankings
     */
    private function paginateInvestorRankings(Collection $rankings, int $perPage, ?string $search): LengthAwarePaginator
    {
        $like = SqlLike::term($search);
        $userIds = $rankings->pluck('user_id')->all();

        if ($userIds === []) {
            return $this->emptyTopInvestorsPaginator($perPage);
        }

        $users = User::query()
            ->whereIn('id', $userIds)
            ->where('is_active', true)
            ->when($like, function ($q) use ($like): void {
                $q->where(function ($w) use ($like): void {
                    $w->where('name', 'like', $like)
                        ->orWhere('email', 'like', $like);
                });
            })
            ->get(['id', 'name', 'email', 'image'])
            ->keyBy('id');

        $rows = $rankings
            ->filter(fn (array $row) => $users->has($row['user_id']))
            ->map(function (array $row) use ($users): object {
                $user = $users->get($row['user_id']);

                return (object) [
                    'user_id' => $row['user_id'],
                    'name' => (string) $user->name,
                    'email' => (string) $user->email,
                    'profile_image_url' => PublicMediaUrl::forPath($user->image ?? null),
                    'total_contribution' => $this->decimalString($row['capital']),
                    'total_profit' => $this->decimalString($row['profit_til_today']),
                    'profit_til_today' => $this->decimalString($row['profit_til_today']),
                    'total_projected_profit' => $this->decimalString($row['projected_profit']),
                    'total_til_today' => $this->decimalString($row['total_til_today']),
                    'pools_count' => (int) $row['pools_count'],
                ];
            })
            ->values();

        $page = max(1, (int) request()->query('top_investors_page', 1));
        $total = $rows->count();

        return (new Paginator(
            $rows->forPage($page, $perPage)->values(),
            $total,
            $perPage,
            $page,
            [
                'path' => Paginator::resolveCurrentPath(),
                'pageName' => 'top_investors_page',
            ]
        ))->withQueryString();
    }

    private function emptyTopInvestorsPaginator(int $perPage): LengthAwarePaginator
    {
        return (new Paginator(
            collect(),
            0,
            $perPage,
            1,
            [
                'pageName' => 'top_investors_page',
                'path' => Paginator::resolveCurrentPath(),
            ]
        ))->withQueryString();
    }

    private function decimalString(mixed $value): string
    {
        if ($value === null || $value === '') {
            return '0.00';
        }

        return number_format((float) $value, 2, '.', '');
    }
}
