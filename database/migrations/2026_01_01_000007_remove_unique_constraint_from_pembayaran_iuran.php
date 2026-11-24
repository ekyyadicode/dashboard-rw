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
        Schema::table('pembayaran_iuran', function (Blueprint $table) {
            // Tambah index biasa pada kartu_keluarga_id terlebih dahulu agar FK MySQL tetap memiliki index sebelum unique index dihapus
            $table->index('kartu_keluarga_id');
            $table->dropUnique('iuran_kk_jenis_periode_unique');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('pembayaran_iuran', function (Blueprint $table) {
            $table->unique(
                ['kartu_keluarga_id', 'jenis_iuran_id', 'periode_bulan', 'periode_tahun'],
                'iuran_kk_jenis_periode_unique'
            );
            $table->dropIndex(['kartu_keluarga_id']);
        });
    }
};

