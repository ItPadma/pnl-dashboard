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
        Schema::connection('sqlsrv')->create('access_group_menu', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('access_group_id');
            $table->unsignedBigInteger('menu_id');
            $table->timestamps();

            // Foreign keys
            $table->foreign('access_group_id')
                  ->references('id')
                  ->on('access_groups')
                  ->onDelete('cascade');

            $table->foreign('menu_id')
                  ->references('id')
                  ->on('menus')
                  ->onDelete('cascade');

            // Unique constraint to prevent duplicate assignments
            $table->unique(['access_group_id', 'menu_id'], 'unique_group_menu');

            // Indexes for better performance
            $table->index('menu_id');
            $table->index('access_group_id');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::connection('sqlsrv')->dropIfExists('access_group_menu');
    }
};
