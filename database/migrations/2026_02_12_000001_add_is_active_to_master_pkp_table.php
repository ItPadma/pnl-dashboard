<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::table('master_pkp', function (Blueprint $table) {
            $table->boolean('is_active')->default(true)->after('TypePajak');
        });

        DB::table('master_pkp')->whereNull('is_active')->update(['is_active' => true]);
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('master_pkp', function (Blueprint $table) {
            $table->dropColumn('is_active');
        });
    }
};
