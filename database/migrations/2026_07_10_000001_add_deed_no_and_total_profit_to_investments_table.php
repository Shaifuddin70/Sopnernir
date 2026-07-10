<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('investments', function (Blueprint $table) {
            $table->string('deed_no', 64)->nullable()->after('title');
            $table->decimal('total_profit_amount', 14, 2)->nullable()->after('default_monthly_rate_pct');
        });
    }

    public function down(): void
    {
        Schema::table('investments', function (Blueprint $table) {
            $table->dropColumn(['deed_no', 'total_profit_amount']);
        });
    }
};
