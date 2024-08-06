<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class KategoriPendidikan extends Model
{
    use HasFactory;

    protected $guarded = ['id'];

    /**
     * Get all of the data_karaywans for the KategoriPendidikan
     *
     * @return \Illuminate\Database\Eloquent\Relations\HasMany
     */
    public function data_karaywans(): HasMany
    {
        return $this->hasMany(DataKaryawan::class, 'pendidikan_terakhir', 'id');
    }
}
