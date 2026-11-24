<?php

namespace App\Models;

use App\Traits\BelongsToRt;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\SoftDeletes;

class BankSampahTransaksi extends Model
{
    use BelongsToRt, HasFactory, SoftDeletes;

    protected $table = 'bank_sampah_transaksi';

    protected $fillable = [
        'rt_id',
        'warga_id',
        'jenis_transaksi',
        'berat_kg',
        'nominal',
        'saldo_akhir',
        'tanggal_transaksi',
        'catatan',
        'extra_data',
        'created_by',
    ];

    protected function casts(): array
    {
        return [
            'extra_data' => 'array',
            'tanggal_transaksi' => 'date',
        ];
    }

    public function rt(): BelongsTo
    {
        return $this->belongsTo(Rt::class, 'rt_id');
    }

    public function warga(): BelongsTo
    {
        return $this->belongsTo(Warga::class, 'warga_id');
    }

    public function creator(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by');
    }
}

