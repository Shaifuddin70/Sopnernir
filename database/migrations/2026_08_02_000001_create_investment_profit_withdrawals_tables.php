<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('investment_profit_withdrawals', function (Blueprint $table) {
            $table->id();
            $table->foreignId('investment_id')->constrained()->cascadeOnDelete();
            $table->date('through_date');
            $table->decimal('amount', 14, 2);
            $table->decimal('profit_through_date', 14, 2);
            $table->text('notes')->nullable();
            $table->foreignId('withdrawn_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamp('withdrawn_at');
            $table->timestamps();

            $table->index(['investment_id', 'through_date']);
        });

        Schema::create('investment_profit_withdrawal_lines', function (Blueprint $table) {
            $table->id();
            $table->foreignId('withdrawal_id')
                ->constrained('investment_profit_withdrawals')
                ->cascadeOnDelete();
            $table->foreignId('user_id')->constrained()->cascadeOnDelete();
            $table->decimal('contribution_amount', 14, 2);
            $table->decimal('amount', 14, 2);
            $table->timestamps();

            $table->unique(['withdrawal_id', 'user_id']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('investment_profit_withdrawal_lines');
        Schema::dropIfExists('investment_profit_withdrawals');
    }
};
