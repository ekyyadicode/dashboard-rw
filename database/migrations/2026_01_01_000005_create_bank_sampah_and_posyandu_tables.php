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
        Schema::create('bank_sampah_transaksi', function (Blueprint $table) {
            $table->id();
            $table->foreignId('rt_id')->constrained('rts')->cascadeOnDelete();
            $table->foreignId('warga_id')->constrained('warga')->cascadeOnDelete();
            $table->string('jenis_transaksi', 20);
            $table->decimal('berat_kg', 8, 2)->nullable();
            $table->unsignedInteger('nominal');
            $table->unsignedBigInteger('saldo_akhir');
            $table->date('tanggal_transaksi');
            $table->string('catatan')->nullable();
            $table->json('extra_data')->nullable();
            $table->foreignId('created_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamps();
            $table->softDeletes();

            $table->index(['rt_id', 'tanggal_transaksi']);
        });

        Schema::create('posyandu_balita', function (Blueprint $table) {
            $table->id();
            $table->foreignId('rt_id')->constrained('rts')->cascadeOnDelete();
            $table->foreignId('warga_id')->constrained('warga')->cascadeOnDelete();
            $table->date('tanggal_periksa');
            $table->unsignedTinyInteger('usia_bulan');
            $table->decimal('berat_badan_kg', 5, 2);
            $table->decimal('tinggi_badan_cm', 5, 2);
            $table->string('imunisasi')->nullable();
            $table->string('catatan')->nullable();
            $table->json('extra_data')->nullable();
            $table->foreignId('created_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamps();
            $table->softDeletes();

            $table->index(['rt_id', 'tanggal_periksa']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('posyandu_balita');
        Schema::dropIfExists('bank_sampah_transaksi');
    }
};

