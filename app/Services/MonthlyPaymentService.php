<?php

namespace App\Services;

use App\Models\Investment;
use App\Models\InvestmentParticipant;
use App\Models\InvestorMonthlyPayment;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Support\Collection;

class MonthlyPaymentService
{
    /**
     * @return Collection<int, array{
     *     user_id: int,
     *     name: string,
     *     email: string,
     *     phone: string|null,
     *     is_paid: bool,
     *     paid_at: string|null,
     * }>
     */
    public function rowsForMonth(Carbon $month): Collection
    {
        $monthStart = $month->copy()->startOfMonth();

        $payments = InvestorMonthlyPayment::query()
            ->whereDate('month', $monthStart->toDateString())
            ->get()
            ->keyBy('user_id');

        return User::query()
            ->where('is_active', true)
            ->orderBy('name')
            ->get()
            ->map(function (User $user) use ($payments): array {
                /** @var InvestorMonthlyPayment|null $payment */
                $payment = $payments->get($user->id);

                return [
                    'user_id' => $user->id,
                    'name' => $user->name,
                    'email' => $user->email,
                    'phone' => $user->phone,
                    'is_paid' => $payment?->isPaid() ?? false,
                    'paid_at' => $payment?->paid_at?->format('Y-m-d H:i'),
                ];
            });
    }

    /**
     * @return Collection<int, int>
     */
    public function paidUserIdsForMonth(Carbon $month): Collection
    {
        $monthStart = $month->copy()->startOfMonth();

        return InvestorMonthlyPayment::query()
            ->whereDate('month', $monthStart->toDateString())
            ->whereNotNull('paid_at')
            ->pluck('user_id');
    }

    public function paidCountForMonth(Carbon $month): int
    {
        return $this->paidUserIdsForMonth($month)->count();
    }

    public function setPaidStatus(User $user, Carbon $month, bool $paid, User $recordedBy): InvestorMonthlyPayment
    {
        $monthStart = $month->copy()->startOfMonth();

        $payment = InvestorMonthlyPayment::query()->firstOrNew([
            'user_id' => $user->id,
            'month' => $monthStart->toDateString(),
        ]);

        if ($paid) {
            $payment->paid_at = $payment->paid_at ?? now();
            $payment->recorded_by = $recordedBy->id;
        } else {
            $payment->paid_at = null;
            $payment->recorded_by = null;
        }

        $payment->save();

        return $payment;
    }

    /**
     * @param  list<int>  $userIds
     */
    public function setPaidStatusForUsers(array $userIds, Carbon $month, bool $paid, User $recordedBy): int
    {
        $updated = 0;

        foreach (User::query()->whereIn('id', $userIds)->get() as $user) {
            $this->setPaidStatus($user, $month, $paid, $recordedBy);
            $updated++;
        }

        return $updated;
    }

    public function tagPaidInvestorsForMonth(Investment $investment, Carbon $month): int
    {
        if ($investment->contribution_per_investor === null || (float) $investment->contribution_per_investor <= 0) {
            return 0;
        }

        $contribution = number_format((float) $investment->contribution_per_investor, 2, '.', '');
        $tagged = 0;

        foreach ($this->paidUserIdsForMonth($month) as $userId) {
            InvestmentParticipant::updateOrCreate(
                [
                    'investment_id' => $investment->id,
                    'user_id' => (int) $userId,
                ],
                [
                    'contribution_amount' => $contribution,
                ]
            );

            $tagged++;
        }

        return $tagged;
    }
}
