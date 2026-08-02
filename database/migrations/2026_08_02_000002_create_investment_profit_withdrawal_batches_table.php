<?php

use App\Models\InvestmentProfitWithdrawal;
use App\Models\InvestmentProfitWithdrawalBatch;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('investment_profit_withdrawal_batches', function (Blueprint $table) {
            $table->id();
            $table->date('through_date');
            $table->decimal('amount', 14, 2);
            $table->unsignedInteger('pools_count')->default(1);
            $table->text('notes')->nullable();
            $table->foreignId('withdrawn_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamp('withdrawn_at');
            $table->timestamps();
        });

        Schema::table('investment_profit_withdrawals', function (Blueprint $table) {
            $table->foreignId('batch_id')
                ->nullable()
                ->after('id')
                ->constrained('investment_profit_withdrawal_batches')
                ->cascadeOnDelete();
        });

        InvestmentProfitWithdrawal::query()
            ->orderBy('id')
            ->each(function (InvestmentProfitWithdrawal $withdrawal): void {
                $batch = InvestmentProfitWithdrawalBatch::query()->create([
                    'through_date' => $withdrawal->through_date,
                    'amount' => $withdrawal->amount,
                    'pools_count' => 1,
                    'notes' => $withdrawal->notes,
                    'withdrawn_by' => $withdrawal->withdrawn_by,
                    'withdrawn_at' => $withdrawal->withdrawn_at ?? $withdrawal->created_at ?? now(),
                ]);

                $withdrawal->forceFill(['batch_id' => $batch->id])->saveQuietly();
            });
    }

    public function down(): void
    {
        Schema::table('investment_profit_withdrawals', function (Blueprint $table) {
            $table->dropConstrainedForeignId('batch_id');
        });

        Schema::dropIfExists('investment_profit_withdrawal_batches');
    }
};
