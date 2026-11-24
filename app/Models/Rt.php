<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Rt extends Model
{
    use HasFactory;

    protected $fillable = [
        'nomor_rt',
        'nama_ketua_rt',
        'no_hp_ketua',
    ];

    public function users(): HasMany
    {
        return $this->hasMany(User::class, 'rt_id');
    }

    public function kartuKeluarga(): HasMany
    {
        return $this->hasMany(KartuKeluarga::class, 'rt_id');
    }

    public function warga(): HasMany
    {
        return $this->hasMany(Warga::class, 'rt_id');
    }

    public function jenisIuran(): HasMany
    {
        return $this->hasMany(JenisIuran::class, 'rt_id');
    }
}

