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
        Schema::create('rekap_absensi_gaji', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('user_id');
            $table->date('periode_awal');
            $table->date('periode_akhir');
            $table->integer('total_hari_kerja')->default(0);
            $table->decimal('total_jam_kerja', 8, 2)->default(0);
            $table->integer('total_keterlambatan_menit')->default(0);
            $table->integer('total_pulang_cepat_menit')->default(0);
            $table->integer('total_tidak_hadir')->default(0);
            $table->integer('total_izin')->default(0);
            $table->decimal('gaji_per_jam', 10, 2)->default(0);
            $table->decimal('total_gaji_kotor', 12, 2)->default(0);
            $table->decimal('potongan_keterlambatan', 10, 2)->default(0);
            $table->decimal('potongan_tidak_hadir', 10, 2)->default(0);
            $table->decimal('total_gaji_bersih', 12, 2)->default(0);
            $table->enum('status_pembayaran', ['belum_dibayar', 'sudah_dibayar', 'pending'])->default('belum_dibayar');
            $table->text('catatan')->nullable();
            $table->timestamps();

            // Foreign key constraint
            $table->foreign('user_id')->references('id')->on('users')->onDelete('cascade');
            
            // Index untuk performa
            $table->index(['user_id', 'periode_awal', 'periode_akhir']);
            $table->index('status_pembayaran');
            
            // Unique constraint untuk mencegah duplikasi rekap periode yang sama
            $table->unique(['user_id', 'periode_awal', 'periode_akhir'], 'unique_user_periode');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('rekap_absensi_gajis');
    }
};
