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
        Schema::create('master_pkp', function (Blueprint $table) {
            $table->id();
            $table->string('IDPelanggan')->nullable();
            $table->string('NamaPKP')->nullable();
            $table->string('AlamatPKP')->nullable();
            $table->string('NoPKP')->nullable();
            $table->string('TypePajak')->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('master_pkp');
    }
};
