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
        Schema::create('gajis', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('user_id');
            $table->unsignedBigInteger('setting_gaji_id');
            $table->date('periode_awal');
            $table->date('periode_akhir');
            $table->integer('total_jam_kerja')->default(0);
            $table->decimal('gaji_per_jam', 10, 2);
            $table->decimal('total_gaji', 15, 2);
            $table->string('status_pembayaran')->default('belum_dibayar');
            $table->text('catatan')->nullable();
            $table->timestamps();

            // Foreign key constraints
            $table->foreign('user_id')->references('id')->on('users')->onDelete('cascade');
            $table->foreign('setting_gaji_id')->references('id')->on('setting_gajis')->onDelete('cascade');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('gajis');
    }
};
