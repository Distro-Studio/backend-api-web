<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class TransferKaryawan extends Model
{
    use HasFactory;
    protected $guarded = ['id'];

    /**
     * Get the user that owns the TransferKaryawan
     *
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
     */
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class, 'user_id', 'id');
    }

    /**
     * Get the unit_kerja_from that owns the TransferKaryawan
     *
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
     */
    public function unit_kerja_from(): BelongsTo
    {
        return $this->belongsTo(UnitKerja::class, 'unit_kerja_from', 'id');
    }

    /**
     * Get the unit_kerja_to that owns the TransferKaryawan
     *
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
     */
    public function unit_kerja_to(): BelongsTo
    {
        return $this->belongsTo(UnitKerja::class, 'unit_kerja_to', 'id');
    }

    /**
     * Get the jabatan_from that owns the TransferKaryawan
     *
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
     */
    public function jabatan_from(): BelongsTo
    {
        return $this->belongsTo(Jabatan::class, 'jabatan_from', 'id');
    }

    /**
     * Get the jabatan_to that owns the TransferKaryawan
     *
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
     */
    public function jabatan_to(): BelongsTo
    {
        return $this->belongsTo(Jabatan::class, 'jabatan_to', 'id');
    }
}
