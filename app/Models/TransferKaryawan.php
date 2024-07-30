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
    public function users(): BelongsTo
    {
        return $this->belongsTo(User::class, 'user_id', 'id');
    }

    /**
     * Get the unit_kerja_asal that owns the TransferKaryawan
     *
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
     */
    public function unit_kerja_asals(): BelongsTo
    {
        return $this->belongsTo(UnitKerja::class, 'unit_kerja_asal', 'id');
    }

    /**
     * Get the unit_kerja_tujuan that owns the TransferKaryawan
     *
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
     */
    public function unit_kerja_tujuans(): BelongsTo
    {
        return $this->belongsTo(UnitKerja::class, 'unit_kerja_tujuan', 'id');
    }

    /**
     * Get the jabatan_asal that owns the TransferKaryawan
     *
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
     */
    public function jabatan_asals(): BelongsTo
    {
        return $this->belongsTo(Jabatan::class, 'jabatan_asal', 'id');
    }

    /**
     * Get the jabatan_tujuan that owns the TransferKaryawan
     *
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
     */
    public function jabatan_tujuans(): BelongsTo
    {
        return $this->belongsTo(Jabatan::class, 'jabatan_tujuan', 'id');
    }

    /**
     * Get the kategori_transfer_karyawans that owns the TransferKaryawan
     *
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
     */
    public function kategori_transfer_karyawans(): BelongsTo
    {
        return $this->belongsTo(KategoriTransferKaryawan::class, 'kategori_transfer_id', 'id');
    }
}
