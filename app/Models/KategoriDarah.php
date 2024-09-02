<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class KategoriDarah extends Model
{
    use HasFactory;
    protected $guarded = ['id'];

    protected $casts = [
        'id' => 'integer',
        'label' => 'string'
    ];

    /**
     * Get all of the data_karyawans for the KategoriDarah
     *
     * @return \Illuminate\Database\Eloquent\Relations\HasMany
     */
    public function data_karyawans(): HasMany
    {
        return $this->hasMany(DataKaryawan::class, 'kategori_darah_id', 'id');
    }

    /**
     * Get all of the perubahan_personals for the KategoriDarah
     *
     * @return \Illuminate\Database\Eloquent\Relations\HasMany
     */
    public function perubahan_personal_darahs(): HasMany
    {
        return $this->hasMany(PerubahanPersonal::class, 'kategori_darah_id', 'id');
    }
}
