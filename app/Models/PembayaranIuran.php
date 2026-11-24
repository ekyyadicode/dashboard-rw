<?php

namespace App\Models;

use App\Traits\BelongsToRt;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\SoftDeletes;

class PembayaranIuran extends Model
{
    use BelongsToRt, HasFactory, SoftDeletes;

    protected $table = 'pembayaran_iuran';

    protected $fillable = [
        'rt_id',
        'kartu_keluarga_id',
        'jenis_iuran_id',
        'periode_bulan',
        'periode_tahun',
        'nominal_dibayar',
        'metode_bayar',
        'tanggal_bayar',
        'catatan',
        'extra_data',
        'created_by',
    ];

    protected function casts(): array
    {
        return [
            'extra_data' => 'array',
            'tanggal_bayar' => 'date',
        ];
    }

    public function rt(): BelongsTo
    {
        return $this->belongsTo(Rt::class, 'rt_id');
    }

    public function kartuKeluarga(): BelongsTo
    {
        return $this->belongsTo(KartuKeluarga::class, 'kartu_keluarga_id');
    }

    public function jenisIuran(): BelongsTo
    {
        return $this->belongsTo(JenisIuran::class, 'jenis_iuran_id');
    }

    public function creator(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by');
    }
}

