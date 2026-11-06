<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class MonthlyPaymentDefault extends Model
{
    protected $fillable = [
        'month',
        'amount',
    ];

    protected function casts(): array
    {
        return [
            'month' => 'date',
            'amount' => 'decimal:2',
        ];
    }
}
