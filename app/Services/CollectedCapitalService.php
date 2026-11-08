<?php

namespace App\Services;

use App\Models\Investment;
use App\Models\InvestorMonthlyPayment;
use App\Models\MonthlyPaymentDefault;
use App\Models\SpecialInstallmentPayment;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Support\Collection;

class CollectedCapitalService
{
    /**
     * Money collected from monthly + special payments (not necessarily invested yet).
     *
     * @return array{
     *     monthly: string,
     *     special: string,
     *     total: string,
     * }
     */
    public function forUser(User $user): array
    {
        $monthly = $this->monthlyTotalForUserIds([$user->id]);
        $special = $this->specialTotalForUserIds([$user->id]);

        return $this->formatTotals($monthly, $special);
    }

    /**
     * Platform-wide collected capital from all paid monthly + special payments.
     *
     * @return array{
     *     monthly: string,
     *     special: string,
     *     total: string,
     * }
     */
    public function platform(): array
    {
        $monthly = $this->monthlyTotalForUserIds(null);
        $special = $this->specialTotalForUserIds(null);

        return $this->formatTotals($monthly, $special);
    }

    /**
     * @param  list<int>|null  $userIds  null = all users
     */
    private function monthlyTotalForUserIds(?array $userIds): float
    {
        $payments = InvestorMonthlyPayment::query()
            ->whereNotNull('paid_at')
            ->when($userIds !== null, fn ($query) => $query->whereIn('user_id', $userIds))
            ->get(['user_id', 'month']);

        if ($payments->isEmpty()) {
            return 0.0;
        }

        $monthKeys = $payments
            ->map(fn (InvestorMonthlyPayment $payment) => $payment->month->copy()->startOfMonth()->toDateString())
            ->unique()
            ->values()
            ->all();

        $defaults = MonthlyPaymentDefault::query()
            ->whereIn('month', $monthKeys)
            ->get()
            ->keyBy(fn (MonthlyPaymentDefault $default) => $default->month->toDateString());

        $poolsByMonth = $this->poolsByPaymentMonth($monthKeys, $userIds);

        $total = 0.0;

        foreach ($payments as $payment) {
            $key = $payment->month->copy()->startOfMonth()->toDateString();
            $amount = $defaults->get($key)?->amount;

            if ($amount === null) {
                $pool = $poolsByMonth->get($key, collect())->first();
                $participant = $pool?->participants
                    ->firstWhere('user_id', $payment->user_id);
                $amount = $participant?->contribution_amount ?? $pool?->contribution_per_investor;
            }

            if ($amount !== null) {
                $total += (float) $amount;
            }
        }

        return $total;
    }

    /**
     * @param  list<int>|null  $userIds
     */
    private function specialTotalForUserIds(?array $userIds): float
    {
        return (float) SpecialInstallmentPayment::query()
            ->whereNotNull('paid_at')
            ->when($userIds !== null, fn ($query) => $query->whereIn('user_id', $userIds))
            ->join('special_installments', 'special_installments.id', '=', 'special_installment_payments.special_installment_id')
            ->sum('special_installments.amount');
    }

    /**
     * @param  list<string>  $monthKeys
     * @param  list<int>|null  $userIds
     * @return Collection<string, Collection<int, Investment>>
     */
    private function poolsByPaymentMonth(array $monthKeys, ?array $userIds): Collection
    {
        if ($monthKeys === []) {
            return collect();
        }

        return Investment::query()
            ->where(function ($query) use ($monthKeys) {
                $query->whereIn('tagged_payment_month', $monthKeys)
                    ->orWhere(function ($query) use ($monthKeys) {
                        $query->whereNull('tagged_payment_month')
                            ->where(function ($query) use ($monthKeys) {
                                foreach ($monthKeys as $key) {
                                    $start = Carbon::parse($key)->startOfMonth();
                                    $query->orWhereBetween('period_start', [
                                        $start->toDateString(),
                                        $start->copy()->endOfMonth()->toDateString(),
                                    ]);
                                }
                            });
                    });
            })
            ->with([
                'participants' => function ($query) use ($userIds) {
                    if ($userIds !== null) {
                        $query->whereIn('user_id', $userIds);
                    }
                },
            ])
            ->get()
            ->groupBy(function (Investment $investment) {
                $month = $investment->tagged_payment_month ?? $investment->period_start;

                return $month?->copy()->startOfMonth()->toDateString();
            });
    }

    /**
     * @return array{monthly: string, special: string, total: string}
     */
    private function formatTotals(float $monthly, float $special): array
    {
        return [
            'monthly' => number_format($monthly, 2, '.', ''),
            'special' => number_format($special, 2, '.', ''),
            'total' => number_format($monthly + $special, 2, '.', ''),
        ];
    }
}
