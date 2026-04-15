<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('investment_participants', function (Blueprint $table) {
            $table->id();
            $table->foreignId('investment_id')->constrained()->cascadeOnDelete();
            $table->foreignId('user_id')->constrained()->cascadeOnDelete();
            $table->decimal('contribution_amount', 15, 2);
            $table->date('effective_from')->nullable();
            $table->timestamps();

            $table->unique(['investment_id', 'user_id']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('investment_participants');
    }
};
