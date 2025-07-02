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
            
            // Foreign Keys
            $table->unsignedBigInteger('user_id');
            $table->unsignedBigInteger('gaji_id')->nullable();
            $table->unsignedBigInteger('setting_gaji_id');
            
            // Periode Rekap
            $table->date('periode_awal');
            $table->date('periode_akhir');
            $table->string('bulan_tahun', 7); // Format: YYYY-MM (contoh: 2025-06)
            
            // Data Absensi Summary
            $table->integer('total_hari_kerja')->default(0);
            $table->integer('total_hadir')->default(0);
            $table->integer('total_sakit')->default(0);
            $table->integer('total_izin')->default(0);
            $table->integer('total_alpha')->default(0); // tidak hadir tanpa keterangan
            $table->integer('total_terlambat')->default(0);
            
            // Data Waktu Kerja
            $table->decimal('total_jam_kerja', 8, 2)->default(0); // dalam jam
            $table->integer('total_menit_kerja')->default(0); // dalam menit
            
            // Data Gaji
            $table->decimal('gaji_per_jam', 10, 2)->default(0);
            $table->decimal('total_gaji', 12, 2)->default(0); // total gaji = total_jam_kerja * gaji_per_jam
            
            // Status dan Keterangan
            $table->enum('status_rekap', ['draft', 'approved', 'paid'])->default('draft');
            $table->text('keterangan')->nullable();
            $table->boolean('is_final')->default(false); // apakah rekap sudah final
            
            // Metadata
            $table->timestamp('tanggal_rekap')->nullable(); // kapan rekap dibuat
            $table->unsignedBigInteger('created_by')->nullable(); // siapa yang buat rekap
            $table->unsignedBigInteger('approved_by')->nullable(); // siapa yang approve
            $table->timestamp('approved_at')->nullable(); // kapan di-approve
            
            $table->timestamps();
            
            // Foreign Key Constraints
            $table->foreign('user_id')->references('id')->on('users')->onDelete('cascade');
            $table->foreign('gaji_id')->references('id')->on('gajis')->onDelete('set null');
            $table->foreign('setting_gaji_id')->references('id')->on('setting_gajis')->onDelete('cascade');
            $table->foreign('created_by')->references('id')->on('users')->onDelete('set null');
            $table->foreign('approved_by')->references('id')->on('users')->onDelete('set null');
            
            // Indexes untuk performance
            $table->index(['user_id', 'periode_awal', 'periode_akhir']);
            $table->index(['bulan_tahun']);
            $table->index(['status_rekap']);
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
