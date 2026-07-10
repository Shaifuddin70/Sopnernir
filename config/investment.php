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

];
