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
            $table->enum('jenis_dokumen', ['peraturan_desa', 'peraturan_kepala_desa', 'peraturan_bersama_kepala_desa'])->nullable(); // biar bisa bedain dua jenis
            $table->string('nomor_ditetapkan')->nullable(); // PERDES
            $table->date('tanggal_ditetapkan')->nullable(); // PERDES
            $table->text('tentang')->nullable(); // PERDES
            $table->integer('nomor_diundangkan')->nullable(); // PERDES -- publish
            $table->date('tanggal_diundangkan')->nullable(); // PERDES -- publish 
            $table->text('keterangan')->nullable(); // 2-2nya
            $table->string('file_upload')->nullable();
            // $table->enum('status', ['Draft', 'Disetujui', 'Ditolak', 'Publish', 'Arsip'])->default('Draft');
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
