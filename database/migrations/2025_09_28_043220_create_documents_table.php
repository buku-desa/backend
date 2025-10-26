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
        Schema::create('documents', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->uuid('id_user');
            $table->unsignedInteger('nomor_urut')->nullable();
            $table->enum('tipe', ['peraturan_desa', 'keputusan_kepala_desa'])->nullable(); // biar bisa bedain dua jenis
            $table->string('jenis_dokumen')->nullable(); // PERDES
            $table->string('nomor_dokumen')->nullable(); // PERDES
            $table->date('tanggal_ditetapkan')->nullable(); // PERDES
            $table->text('tentang')->nullable(); // PERDES
            $table->text('uraian_singkat')->nullable(); // KEKADES
            $table->string('nomor_dan_tanggal_dilaporkan')->nullable(); // KEKADES -- publish
            $table->string('nomor_diundangkan')->nullable(); // PERDES -- publish
            $table->date('tanggal_diundangkan')->nullable(); // PERDES -- publish 
            $table->text('keterangan')->nullable(); // 2-2nya
            $table->string('file_upload')->nullable();
            $table->enum('status', ['Draft', 'Disetujui', 'Ditolak', 'Publish', 'Arsip'])->default('Draft');
            $table->timestamps();

            $table->foreign('id_user')->references('id')->on('users')->onDelete('cascade');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('documents');
    }
};
