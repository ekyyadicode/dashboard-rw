<?php

namespace App\Models;

use App\Traits\BelongsToRt;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;

class Warga extends Model
{
    use BelongsToRt, HasFactory, SoftDeletes;

    protected $table = 'warga';

    protected $fillable = [
        'rt_id',
        'kartu_keluarga_id',
        'nik',
        'nama_lengkap',
        'jenis_kelamin',
        'tanggal_lahir',
        'status_keluarga',
        'no_hp',
        'status_warga',
        'saldo_bank_sampah',
        'extra_data',
        'created_by',
    ];

    protected function casts(): array
    {
        return [
            'extra_data' => 'array',
            'tanggal_lahir' => 'date',
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

    public function bankSampahTransaksi(): HasMany
    {
        return $this->hasMany(BankSampahTransaksi::class, 'warga_id');
    }

    public function creator(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    public function getUsiaBulanAttribute(): ?int
    {
        if (! $this->tanggal_lahir) {
            return null;
        }

        $ref = now()->startOfDay();
        $lahir = $this->tanggal_lahir->copy()->startOfDay();

        if ($lahir->greaterThan($ref)) {
            return 0;
        }

        $diff = $lahir->diff($ref);

        return ($diff->y * 12) + $diff->m;
    }
}
