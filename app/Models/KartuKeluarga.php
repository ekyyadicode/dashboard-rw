<?php

namespace App\Models;

use App\Traits\BelongsToRt;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;
use Illuminate\Database\Eloquent\SoftDeletes;

class KartuKeluarga extends Model
{
    use BelongsToRt, HasFactory, SoftDeletes;

    protected $table = 'kartu_keluarga';

    protected $fillable = [
        'rt_id',
        'no_kk',
        'alamat',
        'nomor_rumah',
        'status_domisili',
        'status_aktif',
        'extra_data',
        'created_by',
    ];

    protected function casts(): array
    {
        return [
            'extra_data' => 'array',
        ];
    }

    public function rt(): BelongsTo
    {
        return $this->belongsTo(Rt::class, 'rt_id');
    }

    public function warga(): HasMany
    {
        return $this->hasMany(Warga::class, 'kartu_keluarga_id');
    }

    public function kepalaKeluarga(): HasOne
    {
        return $this->hasOne(Warga::class, 'kartu_keluarga_id')
            ->where('status_keluarga', 'kepala_keluarga');
    }

    public function pembayaranIuran(): HasMany
    {
        return $this->hasMany(PembayaranIuran::class, 'kartu_keluarga_id');
    }

    public function creator(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by');
    }
}

