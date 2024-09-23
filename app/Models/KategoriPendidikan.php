<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\SoftDeletes;

class KategoriPendidikan extends Model
{
    use HasFactory, SoftDeletes;
    protected $guarded = ['id'];

    protected $casts = [
        'id' => 'integer',
        'label' => 'string'
    ];

    /**
     * Get all of the data_karaywans for the KategoriPendidikan
     *
     * @return \Illuminate\Database\Eloquent\Relations\HasMany
     */
    public function data_karaywans(): HasMany
    {
        return $this->hasMany(DataKaryawan::class, 'pendidikan_terakhir', 'id');
    }

    /**
     * Get all of the perubahan_personal_pendidikan for the KategoriPendidikan
     *
     * @return \Illuminate\Database\Eloquent\Relations\HasMany
     */
    public function perubahan_personal_pendidikans(): HasMany
    {
        return $this->hasMany(PerubahanPersonal::class, 'pendidikan_terakhir', 'id');
    }

        /**
     * Get all of the perubahan_personal_pendidikan for the KategoriPendidikan
     *
     * @return \Illuminate\Database\Eloquent\Relations\HasMany
     */
    public function perubahan_keluarga_pendidikans(): HasMany
    {
        return $this->hasMany(PerubahanKeluarga::class, 'pendidikan_terakhir', 'id');
    }

    /**
     * Get all of the data_kelaurgas for the KategoriPendidikan
     *
     * @return \Illuminate\Database\Eloquent\Relations\HasMany
     */
    public function data_kelaurgas(): HasMany
    {
        return $this->hasMany(DataKeluarga::class, 'pendidikan_terakhir', 'id');
    }
}
