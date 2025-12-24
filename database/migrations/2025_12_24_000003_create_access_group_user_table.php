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
        Schema::connection('sqlsrv')->create('access_group_user', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('access_group_id');
            $table->unsignedBigInteger('user_id');
            $table->tinyInteger('custom_access_level')->nullable()->comment('Override group default. NULL=use group default');
            $table->string('assigned_by')->nullable();
            $table->timestamp('assigned_at')->nullable();
            $table->timestamps();

            // Foreign keys
            $table->foreign('access_group_id')
                  ->references('id')
                  ->on('access_groups')
                  ->onDelete('cascade');

            $table->foreign('user_id')
                  ->references('id')
                  ->on('users')
                  ->onDelete('cascade');

            // Note: No foreign key constraint for assigned_by to avoid SQL Server circular cascade issues
            // Multiple foreign keys to the same table (users) with CASCADE/SET NULL causes conflicts
            // The relationship is maintained at application level through Eloquent

            // Unique constraint to prevent duplicate assignments
            $table->unique(['access_group_id', 'user_id'], 'unique_group_user');

            // Indexes for better performance
            $table->index('user_id');
            $table->index('access_group_id');
            $table->index('assigned_by');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::connection('sqlsrv')->dropIfExists('access_group_user');
    }
};
