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
        Schema::create('jenis_iuran', function (Blueprint $table) {
            $table->id();
            $table->foreignId('rt_id')->constrained('rts')->cascadeOnDelete();
            $table->string('nama_iuran');
            $table->unsignedInteger('nominal');
            $table->string('tipe', 30)->default('bulanan');
            $table->boolean('is_active')->default(true);
            $table->json('extra_data')->nullable();
            $table->foreignId('created_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamps();
            $table->softDeletes();

            $table->index(['rt_id', 'is_active']);
        });

        Schema::create('pembayaran_iuran', function (Blueprint $table) {
            $table->id();
            $table->foreignId('rt_id')->constrained('rts')->cascadeOnDelete();
            $table->foreignId('kartu_keluarga_id')->constrained('kartu_keluarga')->cascadeOnDelete();
            $table->foreignId('jenis_iuran_id')->constrained('jenis_iuran')->restrictOnDelete();
            $table->unsignedTinyInteger('periode_bulan')->nullable();
            $table->unsignedSmallInteger('periode_tahun');
            $table->unsignedInteger('nominal_dibayar');
            $table->string('metode_bayar', 30)->default('tunai');
            $table->date('tanggal_bayar');
            $table->string('catatan')->nullable();
            $table->json('extra_data')->nullable();
            $table->foreignId('created_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamps();
            $table->softDeletes();

            $table->unique(
                ['kartu_keluarga_id', 'jenis_iuran_id', 'periode_bulan', 'periode_tahun'],
                'iuran_kk_jenis_periode_unique'
            );
            $table->index(['rt_id', 'periode_tahun', 'periode_bulan']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('pembayaran_iuran');
        Schema::dropIfExists('jenis_iuran');
    }
};

