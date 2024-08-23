<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Spatie\Permission\Models\Role;

class TransferKaryawan extends Model
{
    use HasFactory;
    protected $guarded = ['id'];

    protected $casts = [
        'id' => 'integer',
        'user_id' => 'integer',
        'unit_kerja_asal' => 'integer',
        'unit_kerja_tujuan' => 'integer',
        'jabatan_asal' => 'integer',
        'jabatan_tujuan' => 'integer',
        'kelompok_gaji_asal' => 'integer',
        'kelompok_gaji_tujuan' => 'integer',
        'role_asal' => 'integer',
        'role_tujuan' => 'integer',
        'kategori_transfer_id' => 'integer'
    ];

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
     * Get the kelompok_gaji_asals that owns the TransferKaryawan
     *
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
     */
    public function kelompok_gaji_asals(): BelongsTo
    {
        return $this->belongsTo(KelompokGaji::class, 'kelompok_gaji_asal', 'id');
    }

    /**
     * Get the kelompok_gaji_tujuans that owns the TransferKaryawan
     *
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
     */
    public function kelompok_gaji_tujuans(): BelongsTo
    {
        return $this->belongsTo(KelompokGaji::class, 'kelompok_gaji_tujuan', 'id');
    }

    /**
     * Get the role_asal that owns the TransferKaryawan
     *
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
     */
    public function role_asals(): BelongsTo
    {
        return $this->belongsTo(Role::class, 'role_asal', 'id');
    }

    /**
     * Get the role_tujuan that owns the TransferKaryawan
     *
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
     */
    public function role_tujuans(): BelongsTo
    {
        return $this->belongsTo(Role::class, 'role_tujuan', 'id');
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
