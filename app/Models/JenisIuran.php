<?php

namespace App\Models;

use App\Traits\BelongsToRt;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;

class JenisIuran extends Model
{
    use BelongsToRt, HasFactory, SoftDeletes;

    protected $table = 'jenis_iuran';

    protected $fillable = [
        'rt_id',
        'nama_iuran',
        'nominal',
        'tipe',
        'is_active',
        'extra_data',
        'created_by',
    ];

    protected function casts(): array
    {
        return [
            'extra_data' => 'array',
            'is_active' => 'boolean',
        ];
    }

    public function rt(): BelongsTo
    {
        return $this->belongsTo(Rt::class, 'rt_id');
    }

    public function pembayaranIuran(): HasMany
    {
        return $this->hasMany(PembayaranIuran::class, 'jenis_iuran_id');
    }

    public function creator(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by');
    }
}

