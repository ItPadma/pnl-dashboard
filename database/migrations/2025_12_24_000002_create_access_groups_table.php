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
        Schema::connection('sqlsrv')->create('access_groups', function (Blueprint $table) {
            $table->id();
            $table->string('name', 100);
            $table->text('description')->nullable();
            $table->tinyInteger('default_access_level')->default(1)->comment('0=No Access, 1=Read, 2=Read&Write, 3=Full, 4=Admin');
            $table->boolean('is_active')->default(true);
            $table->string('created_by')->nullable();
            $table->timestamps();

            // Note: No foreign key constraint for created_by to avoid SQL Server circular cascade issues
            // The relationship is maintained at application level through Eloquent

            // Indexes for better performance
            $table->index('name');
            $table->index('is_active');
            $table->index('created_by');
            $table->index('default_access_level');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::connection('sqlsrv')->dropIfExists('access_groups');
    }
};
