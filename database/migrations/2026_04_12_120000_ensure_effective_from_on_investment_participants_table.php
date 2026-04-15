<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (! Schema::hasColumn('investment_participants', 'effective_from')) {
            Schema::table('investment_participants', function (Blueprint $table) {
                $table->date('effective_from')->nullable()->after('contribution_amount');
            });
        }
    }

    public function down(): void
    {
        if (Schema::hasColumn('investment_participants', 'effective_from')) {
            Schema::table('investment_participants', function (Blueprint $table) {
                $table->dropColumn('effective_from');
            });
        }
    }
};
