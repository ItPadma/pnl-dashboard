<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::connection('sqlsrv')->table('menus', function (Blueprint $table) {
            $table->string('type', 20)->default('item')->after('is_active'); // item, section, separator
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::connection('sqlsrv')->table('menus', function (Blueprint $table) {
            $table->dropColumn('type');
        });
    }
};
