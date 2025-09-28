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
            $table->string('jenis_dokumen');
            $table->string('nomor_dokumen')->nullable();
            $table->date('tanggal_ditetapkan')->nullable();
            $table->text('tentang')->nullable();
            $table->date('tanggal_diundangkan')->nullable();
            $table->string('nomor_diundangkan')->nullable();
            $table->text('keterangan')->nullable();
            $table->string('file_upload')->nullable();
            $table->text('ocr_metadata')->nullable();
            $table->enum('status', ['draft', 'published', 'archived'])->default('draft');
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
