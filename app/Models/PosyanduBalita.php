<?php

namespace App\Models;

use App\Traits\BelongsToRt;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\SoftDeletes;

class PosyanduBalita extends Model
{
    use BelongsToRt, HasFactory, SoftDeletes;

    protected $table = 'posyandu_balita';

    protected $fillable = [
        'rt_id',
        'balita_id',
        'tanggal_periksa',
        'usia_bulan',
        'berat_badan_kg',
        'tinggi_badan_cm',
        'imunisasi',
        'catatan',
        'extra_data',
        'created_by',
    ];

    protected function casts(): array
    {
        return [
            'extra_data' => 'array',
            'tanggal_periksa' => 'date',
        ];
    }

    public function rt(): BelongsTo
    {
        return $this->belongsTo(Rt::class, 'rt_id');
    }

    public function balita(): BelongsTo
    {
        return $this->belongsTo(Balita::class, 'balita_id');
    }

    public function creator(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    public function getUsiaFormatAttribute(): string
    {
        $totalBulan = (int) $this->usia_bulan;

        if ($totalBulan < 12) {
            return "{$totalBulan} bln";
        }

        $tahun = intdiv($totalBulan, 12);
        $sisaBulan = $totalBulan % 12;

        return "{$tahun} thn {$sisaBulan} bln ({$totalBulan} bln)";
    }
}
