<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('investment_periods', function (Blueprint $table) {
            $table->id();
            $table->foreignId('investment_id')->constrained()->cascadeOnDelete();
            $table->date('month');
            $table->decimal('applied_rate_pct', 10, 4);
            $table->decimal('principal_snapshot', 18, 2);
            $table->decimal('profit_amount', 18, 2);
            $table->timestamps();

            $table->unique(['investment_id', 'month']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('investment_periods');
    }
};
