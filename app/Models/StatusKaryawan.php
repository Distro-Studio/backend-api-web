<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\SoftDeletes;

class StatusKaryawan extends Model
{
    use HasFactory, SoftDeletes;
    protected $guarded = ['id'];

    protected $casts = [
        'id' => 'integer',
        'label' => 'string'
    ];

    /**
     * Get all of the data_karyawans for the StatusKaryawan
     *
     * @return \Illuminate\Database\Eloquent\Relations\HasMany
     */
    public function data_karyawans(): HasMany
    {
        return $this->hasMany(DataKaryawan::class, 'status_karyawan_id', 'id');
    }

    /**
     * Get all of the jenis_penilaians for the StatusKaryawan
     *
     * @return \Illuminate\Database\Eloquent\Relations\HasMany
     */
    public function jenis_penilaians(): HasMany
    {
        return $this->hasMany(Penilaian::class, 'status_karyawan_id', 'id');
    }
}
