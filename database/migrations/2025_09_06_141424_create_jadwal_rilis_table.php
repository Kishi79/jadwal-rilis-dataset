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
        Schema::create('jadwal_rilis', function (Blueprint $table) {
            $table->id();
            $table->string('dataset_id')->comment('ID dataset dari API');
            $table->string('dataset_judul');
            $table->string('opd_id')->comment('ID OPD dari API');
            $table->string('opd_nama');
            $table->string('sektoral')->nullable();
            $table->string('periode_waktu');
            $table->date('jadwal_rilis');
            $table->enum('status', ['Belum Rilis', 'Sudah Rilis', 'Terlambat'])->default('Belum Rilis');
            $table->text('catatan')->nullable();
            $table->unsignedBigInteger('created_by')->nullable();
            $table->unsignedBigInteger('updated_by')->nullable();
            $table->timestamps();
            
            $table->foreign('created_by')->references('id')->on('users')->onDelete('set null');
            $table->foreign('updated_by')->references('id')->on('users')->onDelete('set null');
            
            $table->index(['dataset_id', 'opd_id']);
            $table->index('jadwal_rilis');
            $table->index('status');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('jadwal_rilis');
    }
};
