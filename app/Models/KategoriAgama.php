<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class KategoriAgama extends Model
{
    use HasFactory;
    protected $guarded = ['id'];

    protected $casts = [
        'id' => 'integer',
        'label' => 'string'
    ];

    /**
     * Get all of the data_karyawans for the KategoriAgama
     *
     * @return \Illuminate\Database\Eloquent\Relations\HasMany
     */
    public function data_karyawans(): HasMany
    {
        return $this->hasMany(DataKaryawan::class, 'kategori_agama_id', 'id');
    }

    /**
     * Get all of the perubahan_personals for the KategoriAgama
     *
     * @return \Illuminate\Database\Eloquent\Relations\HasMany
     */
    public function perubahan_personal_agamas(): HasMany
    {
        return $this->hasMany(PerubahanPersonal::class, 'kategori_agama_id', 'id');
    }
}
