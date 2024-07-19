<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
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
    public function user_dinilai(): BelongsTo
    {
        return $this->belongsTo(User::class, 'user_dinilai', 'id');
    }

    /**
     * Get the user_penilai that owns the Penilaian
     *
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
     */
    public function user_penilai(): BelongsTo
    {
        return $this->belongsTo(User::class, 'user_penilai', 'id');
    }

    /**
     * Get the unit_kerja_dinilai that owns the Penilaian
     *
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
     */
    public function unit_kerja_dinilai(): BelongsTo
    {
        return $this->belongsTo(UnitKerja::class, 'unit_kerja_dinilai', 'id');
    }

    /**
     * Get the unit_kerja_penilai that owns the Penilaian
     *
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
     */
    public function unit_kerja_penilai(): BelongsTo
    {
        return $this->belongsTo(UnitKerja::class, 'unit_kerja_penilai', 'id');
    }

    /**
     * Get the jabatan_dinilai that owns the Penilaian
     *
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
     */
    public function jabatan_dinilai(): BelongsTo
    {
        return $this->belongsTo(Jabatan::class, 'jabatan_dinilai', 'id');
    }
    
    /**
     * Get the jabatan_penilai that owns the Penilaian
     *
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
     */
    public function jabatan_penilai(): BelongsTo
    {
        return $this->belongsTo(Jabatan::class, 'jabatan_penilai', 'id');
    }
}
