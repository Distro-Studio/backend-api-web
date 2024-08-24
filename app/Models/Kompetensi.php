<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class Kompetensi extends Model
{
    use HasFactory, SoftDeletes;
    protected $guarded = ['id'];

    protected $casts = [
        'id' => 'integer',
        'jenis_kompetensi' => 'integer',
        'tunjangan_kompetensi' => 'integer',
        'nilai_bor' => 'integer'
    ];

    /**
     * Get all of the data_karyawan for the Kompetensi
     *
     * @return \Illuminate\Database\Eloquent\Relations\HasMany
     */
    public function data_karyawan(): HasMany
    {
        return $this->hasMany(DataKaryawan::class, 'kompetensi_id', 'id');
    }
}
