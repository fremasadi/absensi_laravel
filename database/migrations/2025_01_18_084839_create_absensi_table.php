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
        Schema::create('absensi', function (Blueprint $table) {
            $table->id(); // Primary Key
            $table->unsignedBigInteger('id_user'); // Foreign Key to users table
            $table->unsignedBigInteger('id_jadwal')->nullable(); // Foreign Key to jadwal_shifts table
            $table->dateTime('waktu_masuk')->nullable();
            $table->dateTime('waktu_keluar')->nullable();
            $table->integer('durasi_terlambat')->nullable()->comment('Durasi terlambat dalam menit');
            $table->string('status_kehadiran')->nullable()->comment('Status hadir seperti Hadir, Izin, dll.');
            $table->string('keterangan')->nullable();
            $table->timestamps();
        
            // Foreign key constraints
            $table->foreign('id_user')->references('id')->on('users')->onDelete('cascade');
            $table->foreign('id_jadwal')->references('id')->on('jadwal_shifts')->onDelete('set null'); // Perbaiki nama tabel
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('absensi');
    }
};
