<?php

namespace App\Models;

use Spatie\Permission\Models\Role;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class JenisPenilaian extends Model
{
    use HasFactory;

    protected $guarded = ['id'];

    /**
     * Get the status_karyawans that owns the JenisPenilaian
     *
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
     */
    public function status_karyawans(): BelongsTo
    {
        return $this->belongsTo(StatusKaryawan::class, 'status_karyawan_id', 'id');
    }

    /**
     * Get the jabatan_penilai that owns the JenisPenilaian
     *
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
     */
    public function jabatan_penilais(): BelongsTo
    {
        return $this->belongsTo(jabatan::class, 'jabatan_penilai', 'id');
    }

    /**
     * Get the jabatan_dinilais that owns the JenisPenilaian
     *
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
     */
    public function jabatan_dinilais(): BelongsTo
    {
        return $this->belongsTo(jabatan::class, 'jabatan_dinilai', 'id');
    }

    /**
     * Get the unit_kerjas that owns the JenisPenilaian
     *
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
     */
    public function unit_kerjas(): BelongsTo
    {
        return $this->belongsTo(UnitKerja::class, 'unit_kerja_id', 'id');
    }

    /**
     * Get all of the penilaians for the JenisPenilaian
     *
     * @return \Illuminate\Database\Eloquent\Relations\HasMany
     */
    public function penilaians(): HasMany
    {
        return $this->hasMany(Penilaian::class, 'jenis_penilaian_id', 'id');
    }
}