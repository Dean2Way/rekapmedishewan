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
        Schema::create('penandatangan', function (Blueprint $table) {
            $table->id('id_penandatangan');
            $table->string('nama', 150);              // sudah termasuk gelar
            $table->string('nip', 30)->unique();
            $table->string('jabatan', 150)->nullable(); // opsional
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('penandatangan');
    }
};
