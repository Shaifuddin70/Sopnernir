<?php

namespace App\Providers;

use App\Models\Investment;
use App\Models\InvestmentParticipant;
use App\Services\InvestmentAccrualService;
use Illuminate\Support\ServiceProvider;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        //
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        InvestmentParticipant::saved(function (InvestmentParticipant $participant): void {
            if (! config('investment.auto_accrue_on_save', true)) {
                return;
            }

            $investment = $participant->investment()->first();
            if (! $investment || $investment->status !== Investment::STATUS_ACTIVE || ! $investment->is_active) {
                return;
            }

            app(InvestmentAccrualService::class)->syncMonthsThrough($investment, Investment::accrualThroughInclusive());
        });

        Investment::updated(function (Investment $investment): void {
            if (! config('investment.auto_accrue_on_save', true)) {
                return;
            }

            if ($investment->status !== Investment::STATUS_ACTIVE || ! $investment->is_active) {
                return;
            }

            // Do not gate on wasChanged('period_start'): date casting / equivalent values can
            // skip a needed sync; rebuilding posted months is idempotent for the current inputs.
            if ($investment->participants()->doesntExist()) {
                return;
            }

            app(InvestmentAccrualService::class)->syncMonthsThrough($investment->fresh(), Investment::accrualThroughInclusive());
        });
    }
}
