<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('balita', function (Blueprint $table) {
            $table->id();
            $table->foreignId('rt_id')->constrained('rts')->cascadeOnDelete();
            $table->string('nama_balita');
            $table->string('nik', 16)->nullable()->unique();
            $table->string('jenis_kelamin', 10);
            $table->date('tanggal_lahir');
            $table->string('nama_ibu');
            $table->string('nama_ayah')->nullable();
            $table->text('alamat')->nullable();
            $table->string('no_hp_ortu', 20)->nullable();
            $table->string('status_aktif', 30)->default('aktif');
            $table->json('extra_data')->nullable();
            $table->foreignId('created_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamps();
            $table->softDeletes();

            $table->index(['rt_id', 'status_aktif']);
        });

        DB::table('posyandu_balita')->truncate();

        Schema::table('posyandu_balita', function (Blueprint $table) {
            $table->dropForeign(['warga_id']);
            $table->dropColumn('warga_id');
            $table->foreignId('balita_id')->after('rt_id')->constrained('balita')->cascadeOnDelete();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        DB::table('posyandu_balita')->truncate();

        Schema::table('posyandu_balita', function (Blueprint $table) {
            $table->dropForeign(['balita_id']);
            $table->dropColumn('balita_id');
            $table->foreignId('warga_id')->after('rt_id')->constrained('warga')->cascadeOnDelete();
        });

        Schema::dropIfExists('balita');
    }
};

