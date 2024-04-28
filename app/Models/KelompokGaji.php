<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class KelompokGaji extends Model
{
    use HasFactory;
    protected $guarded = ['id'];

    /**
     * Get all of the data_karyawan for the KelompokGaji
     *
     * @return \Illuminate\Database\Eloquent\Relations\HasMany
     */
    public function data_karyawan(): HasMany
    {
        return $this->hasMany(DataKaryawan::class, 'kelompok_gaji_id', 'id');
    }
}
