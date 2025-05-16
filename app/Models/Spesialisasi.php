<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;

class Spesialisasi extends Model
{
    use HasFactory, SoftDeletes;
    protected $guarded = ['id'];

    protected $casts = [
        'id' => 'integer',
    ];

    /**
     * Get all of the data_karyawans for the Spesialisasi
     *
     * @return \Illuminate\Database\Eloquent\Relations\HasMany
     */
    public function data_karyawans(): HasMany
    {
        return $this->hasMany(DataKaryawan::class, 'spesialisasi_id', 'id');
    }
}
