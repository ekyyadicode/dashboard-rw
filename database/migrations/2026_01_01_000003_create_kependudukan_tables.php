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
        Schema::create('kartu_keluarga', function (Blueprint $table) {
            $table->id();
            $table->foreignId('rt_id')->constrained('rts')->cascadeOnDelete();
            $table->string('no_kk', 16)->unique();
            $table->text('alamat');
            $table->string('nomor_rumah', 20);
            $table->string('status_domisili', 30)->default('tetap');
            $table->string('status_aktif', 30)->default('aktif');
            $table->json('extra_data')->nullable();
            $table->foreignId('created_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamps();
            $table->softDeletes();

            $table->index(['rt_id', 'status_aktif']);
        });

        Schema::create('warga', function (Blueprint $table) {
            $table->id();
            $table->foreignId('rt_id')->constrained('rts')->cascadeOnDelete();
            $table->foreignId('kartu_keluarga_id')->constrained('kartu_keluarga')->cascadeOnDelete();
            $table->string('nik', 16)->unique();
            $table->string('nama_lengkap');
            $table->string('jenis_kelamin', 10);
            $table->date('tanggal_lahir');
            $table->string('status_keluarga', 30);
            $table->string('no_hp', 20)->nullable();
            $table->string('status_warga', 30)->default('aktif');
            $table->unsignedBigInteger('saldo_bank_sampah')->default(0);
            $table->json('extra_data')->nullable();
            $table->foreignId('created_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamps();
            $table->softDeletes();

            $table->index(['rt_id', 'status_warga']);
            $table->index(['rt_id', 'tanggal_lahir']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('warga');
        Schema::dropIfExists('kartu_keluarga');
    }
};

