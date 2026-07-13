<?php

namespace App\Services;

use App\Models\Investment;
use App\Models\InvestmentParticipant;
use App\Models\InvestmentPeriod;
use App\Models\InvestmentPeriodUser;
use App\Models\User;
use App\Support\PublicMediaUrl;
use App\Support\SqlLike;
use Carbon\Carbon;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Pagination\LengthAwarePaginator as Paginator;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;

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
     *     accrual_periods: int,
     *     participant_rows: int,
     *     as_of_date: string,
     * }
     */
    public function platformSummary(): array
    {
        $daily = $this->dailyProfit->platformSummary();
        $postedProfit = InvestmentPeriod::query()
            ->whereHas('investment', fn ($q) => $q->where('is_active', true))
            ->sum('profit_amount');

        return [
            'investments_total' => (int) Investment::query()->count(),
            'investments_active' => (int) Investment::query()
                ->where('status', Investment::STATUS_ACTIVE)
                ->where('is_active', true)
                ->count(),
            'total_contributions' => $daily['total_capital'],
            'total_projected_profit' => $daily['total_projected_profit'],
            'profit_til_today' => $daily['profit_til_today'],
            // Kept for charts / older callers; equals profit til today so capital-vs-profit matches portfolio.
            'total_profit_distributed' => $daily['profit_til_today'],
            'total_amount' => $daily['total_til_today'],
            'member_count' => $daily['member_count'],
            'per_person_profit_til_today' => $daily['per_person_profit_til_today'],
            'accrual_periods' => (int) InvestmentPeriod::query()
                ->whereHas('investment', fn ($q) => $q->where('is_active', true))
                ->count(),
            'participant_rows' => (int) InvestmentParticipant::query()
                ->whereHas('investment', fn ($q) => $q->where('is_active', true))
                ->count(),
            'posted_profit_total' => $this->decimalString($postedProfit),
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
            // Alias used by older dashboard labels / tests — same as profit til today.
            'total_profit' => $daily['profit_til_today'],
            'total_amount' => $daily['total_til_today'],
            'return_on_tagged_capital_pct' => $contrib > 0
                ? number_format(($profitTilToday / $contrib) * 100, 2, '.', '')
                : null,
            'as_of_date' => $daily['as_of_date'],
        ];
    }

    /**
     * @return LengthAwarePaginator<int, object{user_id: int, name: string, email: string, total_profit: string, total_contribution: string, last_payout_month: string|null}>
     */
    public function topInvestorsByProfitGlobally(int $perPage = 10, ?string $search = null): LengthAwarePaginator
    {
        $like = SqlLike::term($search);

        $rows = DB::table('investment_period_users as ipu')
            ->join('investment_periods as ip', 'ip.id', '=', 'ipu.investment_period_id')
            ->join('investments as inv', 'inv.id', '=', 'ip.investment_id')
            ->join('users as u', 'u.id', '=', 'ipu.user_id')
            ->where('inv.is_active', true)
            ->where('u.is_active', true)
            ->when($like, function ($q) use ($like): void {
                $q->where(function ($w) use ($like): void {
                    $w->where('u.name', 'like', $like)
                        ->orWhere('u.email', 'like', $like);
                });
            })
            ->selectRaw('ipu.user_id, u.name, u.email, MAX(u.image) as image, SUM(ipu.profit_share) as total_profit, MAX(ip.month) as last_payout_month')
            ->groupBy('ipu.user_id', 'u.name', 'u.email')
            ->orderByDesc('total_profit')
            ->paginate($perPage, ['*'], 'top_investors_page')
            ->withQueryString();

        return $this->attachContributionTotals($rows);
    }

    /**
     * Top investors by profit share, limited to investments the viewer participates in.
     *
     * @return LengthAwarePaginator<int, object{user_id: int, name: string, email: string, total_profit: string, total_contribution: string, last_payout_month: string|null}>
     */
    public function topCoInvestorsByProfit(User $viewer, int $perPage = 10, ?string $search = null): LengthAwarePaginator
    {
        $investmentIds = InvestmentParticipant::query()
            ->where('user_id', $viewer->id)
            ->whereHas('investment', fn ($q) => $q->where('is_active', true))
            ->pluck('investment_id');

        if ($investmentIds->isEmpty()) {
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

        $like = SqlLike::term($search);

        $rows = DB::table('investment_period_users as ipu')
            ->join('investment_periods as ip', 'ip.id', '=', 'ipu.investment_period_id')
            ->join('investments as inv', 'inv.id', '=', 'ip.investment_id')
            ->join('users as u', 'u.id', '=', 'ipu.user_id')
            ->whereIn('ip.investment_id', $investmentIds)
            ->where('inv.is_active', true)
            ->where('u.is_active', true)
            ->when($like, function ($q) use ($like): void {
                $q->where(function ($w) use ($like): void {
                    $w->where('u.name', 'like', $like)
                        ->orWhere('u.email', 'like', $like);
                });
            })
            ->selectRaw('ipu.user_id, u.name, u.email, MAX(u.image) as image, SUM(ipu.profit_share) as total_profit, MAX(ip.month) as last_payout_month')
            ->groupBy('ipu.user_id', 'u.name', 'u.email')
            ->orderByDesc('total_profit')
            ->paginate($perPage, ['*'], 'top_investors_page')
            ->withQueryString();

        return $this->attachContributionTotals($rows);
    }

    /**
     * @return array{labels: list<string>, values: list<float>}
     */
    public function monthlyProfitTrendPlatform(int $months = 6): array
    {
        $start = Carbon::now()->subMonths($months - 1)->startOfMonth();

        $totals = InvestmentPeriod::query()
            ->whereHas('investment', fn ($q) => $q->where('is_active', true))
            ->where('month', '>=', $start)
            ->get(['month', 'profit_amount'])
            ->groupBy(fn (InvestmentPeriod $period) => Carbon::parse($period->month)->format('Y-m'))
            ->map(fn (Collection $group) => $group->sum(fn (InvestmentPeriod $period) => (float) $period->profit_amount));

        return $this->fillMonthlySeries($totals, $months);
    }

    /**
     * @return array{labels: list<string>, values: list<float>}
     */
    public function monthlyProfitTrendForUser(User $user, int $months = 6): array
    {
        $start = Carbon::now()->subMonths($months - 1)->startOfMonth();

        $rows = InvestmentPeriodUser::query()
            ->where('investment_period_users.user_id', $user->id)
            ->join('investment_periods as ip', 'ip.id', '=', 'investment_period_users.investment_period_id')
            ->join('investments as inv', 'inv.id', '=', 'ip.investment_id')
            ->where('inv.is_active', true)
            ->where('ip.month', '>=', $start)
            ->get(['ip.month', 'investment_period_users.profit_share']);

        $totals = $rows->groupBy(fn ($row) => Carbon::parse($row->month)->format('Y-m'))
            ->map(fn (Collection $group) => $group->sum(fn ($row) => (float) $row->profit_share));

        return $this->fillMonthlySeries($totals, $months);
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
            'active' => (int) Investment::query()->where('status', Investment::STATUS_ACTIVE)->count(),
            'draft' => (int) Investment::query()->where('status', Investment::STATUS_DRAFT)->count(),
            'closed' => (int) Investment::query()->where('status', Investment::STATUS_CLOSED)->count(),
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
     * @param  Collection<string|int, mixed>  $totalsByYm
     * @return array{labels: list<string>, values: list<float>}
     */
    private function fillMonthlySeries(Collection $totalsByYm, int $months): array
    {
        $labels = [];
        $values = [];
        $cursor = Carbon::now()->subMonths($months - 1)->startOfMonth();
        $end = Carbon::now()->startOfMonth();

        while ($cursor->lte($end)) {
            $key = $cursor->format('Y-m');
            $labels[] = $cursor->translatedFormat('M Y');
            $values[] = round((float) ($totalsByYm[$key] ?? 0), 2);
            $cursor->addMonth();
        }

        return ['labels' => $labels, 'values' => $values];
    }

    private function decimalString(mixed $value): string
    {
        if ($value === null || $value === '') {
            return '0.00';
        }

        return number_format((float) $value, 2, '.', '');
    }

    /**
     * @param  \Illuminate\Contracts\Pagination\LengthAwarePaginator<int, object>  $rows
     * @return LengthAwarePaginator<int, object{user_id: int, name: string, email: string, total_profit: string, total_contribution: string, last_payout_month: string|null}>
     */
    private function attachContributionTotals(LengthAwarePaginator $rows): LengthAwarePaginator
    {
        if ($rows->isEmpty()) {
            return $rows;
        }

        /** @var Paginator<int, object> $paginator */
        $paginator = $rows;

        $items = collect($paginator->items());
        $ids = $items->pluck('user_id')->all();
        $contrib = InvestmentParticipant::query()
            ->whereIn('user_id', $ids)
            ->whereHas('investment', fn ($q) => $q->where('is_active', true))
            ->selectRaw('user_id, SUM(contribution_amount) as total')
            ->groupBy('user_id')
            ->pluck('total', 'user_id');

        $mapped = $items->map(function ($row) use ($contrib): object {
            $tid = (int) $row->user_id;

            return (object) [
                'user_id' => $tid,
                'name' => (string) $row->name,
                'email' => (string) $row->email,
                'profile_image_url' => PublicMediaUrl::forPath($row->image ?? null),
                'total_profit' => $this->decimalString($row->total_profit ?? 0),
                'total_contribution' => $this->decimalString($contrib[$tid] ?? 0),
                'last_payout_month' => $row->last_payout_month,
            ];
        });

        return (new Paginator(
            $mapped,
            $paginator->total(),
            $paginator->perPage(),
            $paginator->currentPage(),
            [
                'path' => $paginator->path(),
                'pageName' => $paginator->getPageName(),
            ]
        ))->withQueryString();
    }
}
