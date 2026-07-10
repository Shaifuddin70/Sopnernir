<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('nominees', function (Blueprint $table) {
            $table->string('nid_number', 64)->nullable()->after('phone');
            $table->unique('nid_number');
        });
    }

    public function down(): void
    {
        Schema::table('nominees', function (Blueprint $table) {
            $table->dropUnique(['nid_number']);
            $table->dropColumn('nid_number');
        });
    }
};
