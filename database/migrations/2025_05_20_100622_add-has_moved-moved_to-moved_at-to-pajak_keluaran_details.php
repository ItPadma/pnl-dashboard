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
        Schema::table('pajak_keluaran_details', function(Blueprint $table) {
            $table->string("has_moved")->default("n")->after("company");
            $table->string("moved_to")->after("has_moved")->nullable();
            $table->timestamp("moved_at")->after("moved_to")->nullable();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('pajak_keluaran_details', function(Blueprint $table) {
            $table->dropColumn("has_moved");
            $table->dropColumn("moved_to");
            $table->dropColumn("moved_at");
        });
    }
};
