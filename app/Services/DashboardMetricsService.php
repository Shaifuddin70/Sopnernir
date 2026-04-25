<?php

namespace App\Services;

use App\Models\Investment;
use App\Models\InvestmentParticipant;
use App\Models\InvestmentPeriod;
use App\Models\InvestmentPeriodUser;
use App\Models\User;
use App\Support\SqlLike;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Pagination\LengthAwarePaginator as Paginator;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;

class DashboardMetricsService
{
    /**
     * @return array{
     *     investments_total: int,
     *     investments_active: int,
     *     total_contributions: string,
     *     total_profit_distributed: string,
     *     total_amount: string,
     *     accrual_periods: int,
     *     participant_rows: int,
     * }
     */
    public function platformSummary(): array
    {
        $contributionSum = InvestmentParticipant::query()
            ->whereHas('investment', fn ($q) => $q->where('is_active', true))
            ->sum('contribution_amount');
        $profitDistributed = InvestmentPeriod::query()
            ->whereHas('investment', fn ($q) => $q->where('is_active', true))
            ->sum('profit_amount');

        return [
            'investments_total' => (int) Investment::query()->count(),
            'investments_active' => (int) Investment::query()
                ->where('status', Investment::STATUS_ACTIVE)
                ->where('is_active', true)
                ->count(),
            'total_contributions' => $this->decimalString($contributionSum),
            'total_profit_distributed' => $this->decimalString($profitDistributed),
            'total_amount' => $this->decimalString($contributionSum + $profitDistributed),
            'accrual_periods' => (int) InvestmentPeriod::query()
                ->whereHas('investment', fn ($q) => $q->where('is_active', true))
                ->count(),
            'participant_rows' => (int) InvestmentParticipant::query()
                ->whereHas('investment', fn ($q) => $q->where('is_active', true))
                ->count(),
        ];
    }

    /**
     * @return array{
     *     investments_count: int,
     *     total_contribution: string,
     *     total_profit: string,
     *     total_amount: string,
     *     return_on_tagged_capital_pct: string|null,
     * }
     */
    public function personalSummary(User $user): array
    {
        $contributionSum = InvestmentParticipant::query()
            ->where('user_id', $user->id)
            ->whereHas('investment', fn ($q) => $q->where('is_active', true))
            ->sum('contribution_amount');
        $profitSum = InvestmentPeriodUser::query()
            ->where('user_id', $user->id)
            ->whereHas('period', fn ($q) => $q->whereHas('investment', fn ($inv) => $inv->where('is_active', true)))
            ->sum('profit_share');
        $contrib = (float) $contributionSum;
        $profit = (float) $profitSum;

        return [
            'investments_count' => (int) InvestmentParticipant::query()
                ->where('user_id', $user->id)
                ->whereHas('investment', fn ($q) => $q->where('is_active', true))
                ->count(),
            'total_contribution' => $this->decimalString($contributionSum),
            'total_profit' => $this->decimalString($profitSum),
            'total_amount' => $this->decimalString($contributionSum + $profitSum),
            'return_on_tagged_capital_pct' => $contrib > 0
                ? number_format(($profit / $contrib) * 100, 2, '.', '')
                : null,
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
            ->selectRaw('ipu.user_id, u.name, u.email, SUM(ipu.profit_share) as total_profit, MAX(ip.month) as last_payout_month')
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
            ->selectRaw('ipu.user_id, u.name, u.email, SUM(ipu.profit_share) as total_profit, MAX(ip.month) as last_payout_month')
            ->groupBy('ipu.user_id', 'u.name', 'u.email')
            ->orderByDesc('total_profit')
            ->paginate($perPage, ['*'], 'top_investors_page')
            ->withQueryString();

        return $this->attachContributionTotals($rows);
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
