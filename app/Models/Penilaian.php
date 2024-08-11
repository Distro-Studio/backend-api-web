<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class Penilaian extends Model
{
    use HasFactory;

    protected $guarded = ['id'];

    /**
     * Get the user_dinilai that owns the Penilaian
     *
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
     */
    public function user_dinilais(): BelongsTo
    {
        return $this->belongsTo(User::class, 'user_dinilai', 'id');
    }

    /**
     * Get the user_penilai that owns the Penilaian
     *
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
     */
    public function user_penilais(): BelongsTo
    {
        return $this->belongsTo(User::class, 'user_penilai', 'id');
    }

    /**
     * Get the unit_kerja_dinilai that owns the Penilaian
     *
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
     */
    public function unit_kerja_dinilais(): BelongsTo
    {
        return $this->belongsTo(UnitKerja::class, 'unit_kerja_dinilai', 'id');
    }

    /**
     * Get the unit_kerja_penilai that owns the Penilaian
     *
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
     */
    public function unit_kerja_penilais(): BelongsTo
    {
        return $this->belongsTo(UnitKerja::class, 'unit_kerja_penilai', 'id');
    }

    /**
     * Get the jabatan_dinilai that owns the Penilaian
     *
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
     */
    public function jabatan_dinilais(): BelongsTo
    {
        return $this->belongsTo(Jabatan::class, 'jabatan_dinilai', 'id');
    }

    /**
     * Get the jabatan_penilai that owns the Penilaian
     *
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
     */
    public function jabatan_penilais(): BelongsTo
    {
        return $this->belongsTo(Jabatan::class, 'jabatan_penilai', 'id');
    }

    /**
     * Get all of the jawabans for the Penilaian
     *
     * @return \Illuminate\Database\Eloquent\Relations\HasMany
     */
    public function jawabans(): HasMany
    {
        return $this->hasMany(Jawaban::class, 'penilaian_id', 'id');
    }
}
