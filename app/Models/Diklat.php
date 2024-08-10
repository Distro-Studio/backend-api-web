<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class Diklat extends Model
{
    use HasFactory;

    protected $guarded = ['id'];

    /**
     * Get the kategori_diklats that owns the Diklat
     *
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
     */
    public function kategori_diklats(): BelongsTo
    {
        return $this->belongsTo(KategoriDiklat::class, 'kategori_diklat_id', 'id');
    }

    /**
     * Get the status_diklats that owns the Diklat
     *
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
     */
    public function status_diklats(): BelongsTo
    {
        return $this->belongsTo(StatusDiklat::class, 'status_diklat_id', 'id');
    }

    /**
     * Get all of the peserta_diklat for the Diklat
     *
     * @return \Illuminate\Database\Eloquent\Relations\HasMany
     */
    public function peserta_diklat(): HasMany
    {
        return $this->hasMany(PesertaDiklat::class, 'diklat_id', 'id');
    }
}
