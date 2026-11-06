<?php

namespace App\Http\Controllers\Investor;

use App\Http\Controllers\Controller;
use App\Models\Investment;
use App\Models\InvestorMonthlyPayment;
use App\Models\MonthlyPaymentDefault;
use App\Models\SpecialInstallmentPayment;
use Illuminate\Support\Facades\Auth;
use Illuminate\View\View;

class PaymentController extends Controller
{
    public function index(): View
    {
        /** @var \App\Models\User $user */
        $user = Auth::user();

        $payments = InvestorMonthlyPayment::query()
            ->where('user_id', $user->id)
            ->whereNotNull('paid_at')
            ->orderByDesc('month')
            ->get();

        $monthKeys = $payments
            ->map(fn (InvestorMonthlyPayment $payment) => $payment->month->copy()->startOfMonth()->toDateString())
            ->all();

        $defaults = MonthlyPaymentDefault::query()
            ->when($monthKeys !== [], fn ($query) => $query->whereIn('month', $monthKeys))
            ->when($monthKeys === [], fn ($query) => $query->whereRaw('0 = 1'))
            ->get()
            ->keyBy(fn (MonthlyPaymentDefault $default) => $default->month->toDateString());

        $investments = Investment::query()
            ->when($monthKeys !== [], fn ($query) => $query->whereIn('tagged_payment_month', $monthKeys))
            ->when($monthKeys === [], fn ($query) => $query->whereRaw('0 = 1'))
            ->with(['participants' => fn ($query) => $query->where('user_id', $user->id)])
            ->orderBy('title')
            ->get()
            ->groupBy(fn (Investment $investment) => $investment->tagged_payment_month->toDateString());

        $rows = collect();

        foreach ($payments as $payment) {
            $key = $payment->month->copy()->startOfMonth()->toDateString();
            $pools = $investments->get($key, collect());

            $defaultAmount = $defaults->get($key)?->amount;
            $defaultAmount = $defaultAmount !== null ? number_format((float) $defaultAmount, 2, '.', '') : null;

            if ($pools->isEmpty()) {
                $rows->push([
                    'month' => $payment->month,
                    'paid_at' => $payment->paid_at,
                    'investment' => null,
                    'amount' => $defaultAmount,
                    'can_view' => false,
                ]);

                continue;
            }

            foreach ($pools as $investment) {
                $participant = $investment->participants->first();
                $amount = $participant?->contribution_amount ?? $investment->contribution_per_investor ?? $defaultAmount;

                $rows->push([
                    'month' => $payment->month,
                    'paid_at' => $payment->paid_at,
                    'investment' => $investment,
                    'amount' => $amount !== null ? number_format((float) $amount, 2, '.', '') : null,
                    'can_view' => $user->can('view', $investment),
                ]);
            }
        }

        $months = $rows
            ->groupBy(fn (array $row) => $row['month']->copy()->startOfMonth()->toDateString())
            ->map(function ($group) {
                $first = $group->first();
                $total = $group->sum(fn (array $row) => (float) ($row['amount'] ?? 0));
                $hasAmount = $group->contains(fn (array $row) => $row['amount'] !== null);

                return [
                    'month' => $first['month'],
                    'paid_at' => $first['paid_at'],
                    'amount' => $hasAmount ? number_format($total, 2, '.', '') : null,
                    'pools' => $group->filter(fn (array $row) => $row['investment'] !== null)->values(),
                ];
            })
            ->values();

        $specials = SpecialInstallmentPayment::query()
            ->where('user_id', $user->id)
            ->whereNotNull('paid_at')
            ->with('installment')
            ->orderByDesc('paid_at')
            ->get();

        $monthlyTotal = $months->sum(fn (array $month) => (float) ($month['amount'] ?? 0));
        $specialTotal = $specials->sum(fn (SpecialInstallmentPayment $payment) => (float) $payment->installment->amount);
        $paidTotal = number_format($monthlyTotal + $specialTotal, 2);
        $poolCount = $months->sum(fn (array $month) => $month['pools']->count());
        $latest = $months->first();

        return view('investor.payments.index', [
            'months' => $months,
            'specials' => $specials,
            'paidMonths' => $payments->count(),
            'paidTotal' => $paidTotal,
            'poolCount' => $poolCount,
            'latestMonth' => $latest['month'] ?? null,
            'latestPaidAt' => $latest['paid_at'] ?? null,
        ]);
    }
}
