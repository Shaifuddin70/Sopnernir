<?php

return [

    /*
    |--------------------------------------------------------------------------
    | Auto-accrue when pool data changes
    |--------------------------------------------------------------------------
    |
    | When true, missing monthly accruals are filled (through the last
    | completed calendar month) whenever an active pool’s participants are
    | saved or when status / first accrual month changes. Disabled in PHPUnit
    | so manual accrual tests stay deterministic.
    |
    */

    'auto_accrue_on_save' => env('INVESTMENT_AUTO_ACCRUE_ON_SAVE', true),

    /*
    |--------------------------------------------------------------------------
    | Profit carry-forward (rollover into new pool principal)
    |--------------------------------------------------------------------------
    |
    | When tagging investors, optional profit is summed from posted accrual rows
    | on other investments from this calendar month onward (inclusive). Set in
    | .env as Y-m (e.g. 2026-01). Leave null to include all posted months.
    | Per-request override: profit_carry_since_month on tag / participant forms.
    |
    | When true, only profit from investments with is_active=true is included
    | (matches typical investor portfolio scope).
    |
    */

    'profit_carry_since_month' => env('INVESTMENT_PROFIT_CARRY_SINCE_MONTH'),

    'profit_carry_only_active_investments' => env('INVESTMENT_PROFIT_CARRY_ONLY_ACTIVE', true),

];
