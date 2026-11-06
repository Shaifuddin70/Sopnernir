<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('special_installments', function (Blueprint $table) {
            $table->id();
            $table->string('title');
            $table->decimal('amount', 14, 2);
            $table->foreignId('created_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamps();
        });

        Schema::create('special_installment_payments', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('special_installment_id');
            $table->foreign('special_installment_id', 'special_installment_payment_fk')
                ->references('id')
                ->on('special_installments')
                ->cascadeOnDelete();
            $table->foreignId('user_id')->constrained()->cascadeOnDelete();
            $table->timestamp('paid_at')->nullable();
            $table->foreignId('recorded_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamps();

            $table->unique(['special_installment_id', 'user_id'], 'special_installment_user_unique');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('special_installment_payments');
        Schema::dropIfExists('special_installments');
    }
};
