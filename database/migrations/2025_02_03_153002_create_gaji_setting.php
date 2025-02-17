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
        // Schema::create('gaji_settings', function (Blueprint $table) {
        //     $table->id();
        //     $table->decimal('gaji_per_jam', 10, 2);  // Gaji dasar per jam
        //     $table->decimal('bonus_lembur', 10, 2)->default(0);  // Bonus lembur per jam
        //     $table->integer('periode_hari')->default(14);  // Periode penggajian (default 14 hari)
        //     $table->timestamps();
        // });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('gaji_setting');
    }
};
