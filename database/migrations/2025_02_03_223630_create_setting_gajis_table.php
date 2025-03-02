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
        Schema::create('setting_gajis', function (Blueprint $table) {
            $table->id(); // ID setting gaji (primary key)
            $table->string('name');
            $table->decimal('gaji_per_jam', 10, 2)->default(10000.00); // Gaji per jam dengan default 10.000
            $table->integer('periode_gaji')->default(14); // Periode gaji dalam hari (default 14 hari)
            $table->timestamps(); // created_at dan updated_at
        
            
        });
        
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('setting_gajis');
    }
};
