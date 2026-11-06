<?php

namespace App\Http\Controllers\Investor;

use App\Http\Controllers\Controller;
use App\Models\Investment;
use App\Models\InvestorMonthlyPayment;
use App\Models\MonthlyPaymentDefault;
use App\Models\SpecialInstallmentPayment;
use Carbon\Carbon;
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

        $pools = Investment::query()
            ->when($monthKeys === [], fn ($query) => $query->whereRaw('0 = 1'))
            ->when($monthKeys !== [], function ($query) use ($monthKeys) {
                $query->where(function ($query) use ($monthKeys) {
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
                });
            })
            ->with(['participants' => fn ($query) => $query->where('user_id', $user->id)])
            ->get()
            ->groupBy(function (Investment $investment) {
                $month = $investment->tagged_payment_month ?? $investment->period_start;

                return $month?->copy()->startOfMonth()->toDateString();
            });

        $months = $payments->map(function (InvestorMonthlyPayment $payment) use ($defaults, $pools) {
            $key = $payment->month->copy()->startOfMonth()->toDateString();
            $amount = $defaults->get($key)?->amount;

            if ($amount === null) {
                $pool = $pools->get($key, collect())->first();
                $participant = $pool?->participants->first();
                $amount = $participant?->contribution_amount ?? $pool?->contribution_per_investor;
            }

            return [
                'month' => $payment->month,
                'paid_at' => $payment->paid_at,
                'amount' => $amount !== null ? number_format((float) $amount, 2, '.', '') : null,
            ];
        })->values();

        $poolCount = Investment::query()
            ->when($monthKeys === [], fn ($query) => $query->whereRaw('0 = 1'))
            ->when($monthKeys !== [], fn ($query) => $query->whereIn('tagged_payment_month', $monthKeys))
            ->whereHas('participants', fn ($query) => $query->where('user_id', $user->id))
            ->count();

        $specials = SpecialInstallmentPayment::query()
            ->where('user_id', $user->id)
            ->whereNotNull('paid_at')
            ->with('installment')
            ->orderByDesc('paid_at')
            ->get();

        $monthlyTotal = $months->sum(fn (array $month) => (float) ($month['amount'] ?? 0));
        $specialTotal = $specials->sum(fn (SpecialInstallmentPayment $payment) => (float) ($payment->installment->amount ?? 0));
        $paidTotal = number_format($monthlyTotal + $specialTotal, 2);
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
