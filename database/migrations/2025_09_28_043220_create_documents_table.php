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
            $table->enum('tipe', ['peraturan_desa', 'keputusan_kepala_desa'])->nullable(); // biar bisa bedain dua jenis
            $table->string('jenis_dokumen')->nullable();
            $table->string('nomor_dokumen')->nullable();
            $table->date('tanggal_ditetapkan')->nullable();
            $table->text('tentang')->nullable();
            $table->text('uraian_singkat')->nullable();
            $table->date('tanggal_dilaporkan')->nullable();
            $table->date('tanggal_diundangkan')->nullable();
            $table->string('nomor_diundangkan')->nullable();
            $table->text('keterangan')->nullable();
            $table->string('file_upload')->nullable();
            $table->enum('status', ['Draft', 'Disetujui', 'Ditolak', 'Arsip'])->default('Draft');
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
