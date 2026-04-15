<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('investment_period_users', function (Blueprint $table) {
            $table->id();
            $table->foreignId('investment_period_id')->constrained()->cascadeOnDelete();
            $table->foreignId('user_id')->constrained()->cascadeOnDelete();
            $table->decimal('contribution_snapshot', 15, 2);
            $table->decimal('profit_share', 18, 2);
            $table->timestamps();

            $table->unique(['investment_period_id', 'user_id']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('investment_period_users');
    }
};
