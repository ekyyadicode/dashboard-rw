<?php

namespace App\Models;

use App\Traits\BelongsToRt;
use Carbon\Carbon;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;
use Illuminate\Database\Eloquent\SoftDeletes;

class Balita extends Model
{
    use BelongsToRt, HasFactory, SoftDeletes;

    protected $table = 'balita';

    protected $fillable = [
        'rt_id',
        'nama_balita',
        'nik',
        'jenis_kelamin',
        'tanggal_lahir',
        'nama_ibu',
        'nama_ayah',
        'alamat',
        'no_hp_ortu',
        'status_aktif',
        'extra_data',
        'created_by',
    ];

    protected function casts(): array
    {
        return [
            'tanggal_lahir' => 'date',
            'extra_data' => 'array',
        ];
    }

    public function rt(): BelongsTo
    {
        return $this->belongsTo(Rt::class, 'rt_id');
    }

    public function posyanduBalita(): HasMany
    {
        return $this->hasMany(PosyanduBalita::class, 'balita_id');
    }

    public function pemeriksaanTerakhir(): HasOne
    {
        return $this->hasOne(PosyanduBalita::class, 'balita_id')->latestOfMany('tanggal_periksa');
    }

    public function creator(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    public function hitungUsiaBulanPada($tanggal = null): int
    {
        if (! $this->tanggal_lahir) {
            return 0;
        }

        $ref = $tanggal ? Carbon::parse($tanggal)->startOfDay() : now()->startOfDay();
        $lahir = $this->tanggal_lahir->copy()->startOfDay();

        if ($lahir->greaterThan($ref)) {
            return 0;
        }

        $diff = $lahir->diff($ref);

        return ($diff->y * 12) + $diff->m;
    }

    public function getUsiaBulanAttribute(): int
    {
        return $this->hitungUsiaBulanPada();
    }

    public function getUsiaFormatAttribute(): string
    {
        $totalBulan = $this->usia_bulan;

        if ($totalBulan < 12) {
            return "{$totalBulan} bln";
        }

        $tahun = intdiv($totalBulan, 12);
        $sisaBulan = $totalBulan % 12;

        return "{$tahun} thn {$sisaBulan} bln ({$totalBulan} bln)";
    }
}

