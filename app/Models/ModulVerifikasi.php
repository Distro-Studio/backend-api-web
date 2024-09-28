<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class ModulVerifikasi extends Model
{
    use HasFactory;

    protected $guarded = ['id'];
    protected $casts = [
        'id' => 'integer',
        'max_order' => 'integer'
    ];

    /**
     * Get all of the relasi_verifikasis for the ModulVerifikasi
     *
     * @return \Illuminate\Database\Eloquent\Relations\HasMany
     */
    public function relasi_verifikasis(): HasMany
    {
        return $this->hasMany(RelasiVerifikasi::class, 'modul_verifikasi', 'id');
    }
}
