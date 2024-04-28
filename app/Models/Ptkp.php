<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class Ptkp extends Model
{
    use HasFactory;

    protected $guarded = ['id'];

    /**
     * Get all of the data_karyawan for the Ptkp
     *
     * @return \Illuminate\Database\Eloquent\Relations\HasMany
     */
    public function data_karyawan(): HasMany
    {
        return $this->hasMany(DataKaryawan::class, 'ptkp_id', 'id');
    }

    /**
     * Get the kategori_ter that owns the Ptkp
     *
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
     */
    public function kategori_ter(): BelongsTo
    {
        return $this->belongsTo(KategoriTer::class, 'kategori_ter_id', 'id');
    }
}
