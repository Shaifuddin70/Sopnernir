<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (Schema::hasColumn('investor_monthly_payments', 'investment_id')) {
            Schema::table('investor_monthly_payments', function (Blueprint $table) {
                $table->dropForeign(['investment_id']);
            });

            $groups = DB::table('investor_monthly_payments')
                ->select('user_id', 'month')
                ->distinct()
                ->get();

            foreach ($groups as $group) {
                $keepId = DB::table('investor_monthly_payments')
                    ->where('user_id', $group->user_id)
                    ->where('month', $group->month)
                    ->orderByRaw('paid_at IS NOT NULL DESC')
                    ->orderBy('id')
                    ->value('id');

                if ($keepId === null) {
                    continue;
                }

                DB::table('investor_monthly_payments')
                    ->where('user_id', $group->user_id)
                    ->where('month', $group->month)
                    ->where('id', '!=', $keepId)
                    ->delete();
            }

            Schema::table('investor_monthly_payments', function (Blueprint $table) {
                $table->dropUnique(['investment_id', 'user_id', 'month']);
                $table->dropColumn('investment_id');
            });
        }

        Schema::table('investor_monthly_payments', function (Blueprint $table) {
            if (! $this->hasUniqueIndex('investor_monthly_payments', 'investor_monthly_payments_user_id_month_unique')) {
                $table->unique(['user_id', 'month']);
            }
        });

        Schema::table('investments', function (Blueprint $table) {
            if (! Schema::hasColumn('investments', 'tagged_payment_month')) {
                $table->date('tagged_payment_month')->nullable()->after('period_start');
            }
        });
    }

    public function down(): void
    {
        Schema::table('investments', function (Blueprint $table) {
            if (Schema::hasColumn('investments', 'tagged_payment_month')) {
                $table->dropColumn('tagged_payment_month');
            }
        });

        Schema::table('investor_monthly_payments', function (Blueprint $table) {
            $table->dropUnique(['user_id', 'month']);
            $table->foreignId('investment_id')->nullable()->after('id')->constrained()->cascadeOnDelete();
            $table->unique(['investment_id', 'user_id', 'month']);
        });
    }

    private function hasUniqueIndex(string $table, string $indexName): bool
    {
        $driver = Schema::getConnection()->getDriverName();

        if ($driver === 'sqlite') {
            $indexes = DB::select("PRAGMA index_list('{$table}')");

            foreach ($indexes as $index) {
                if (($index->name ?? null) === $indexName) {
                    return true;
                }
            }

            return false;
        }

        $database = Schema::getConnection()->getDatabaseName();

        return DB::table('information_schema.statistics')
            ->where('table_schema', $database)
            ->where('table_name', $table)
            ->where('index_name', $indexName)
            ->exists();
    }
};
