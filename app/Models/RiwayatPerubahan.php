<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class RiwayatPerubahan extends Model
{
    use HasFactory;
    protected $guarded = ['id'];

    protected $casts = [
        'id' => 'integer',
        'data_karyawan_id' => 'integer',
        'status_perubahan_id' => 'integer',
        'verifikator_1' => 'integer'
    ];

    protected $integerColumns = [
        'kategori_agama_id',
        'kategori_darah_id',
        'jenis_kelamin',
        'tinggi_badan',
        'berat_badan',
        'tahun_lulus',
        'pendidikan_terakhir'
    ];

    public function getOriginalDataAttribute($value)
    {
        if ($this->jenis_perubahan === 'Keluarga') {
            return json_decode($value, true);
        } elseif (in_array($this->kolom, $this->integerColumns)) {
            return (int)$value; // Convert to integer if column is in integerColumns
        }
        return $value;
    }

    public function getUpdatedDataAttribute($value)
    {
        if ($this->jenis_perubahan === 'Keluarga') {
            return json_decode($value, true);
        } elseif (in_array($this->kolom, $this->integerColumns)) {
            return (int)$value; // Convert to integer if column is in integerColumns
        }
        return $value;
    }

    /**
     * Get the data_karyawans that owns the RiwayatPerubahan
     *
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
     */
    public function data_karyawans(): BelongsTo
    {
        return $this->belongsTo(DataKaryawan::class, 'data_karyawan_id', 'id');
    }

    /**
     * Get the status_perubahans that owns the RiwayatPerubahan
     *
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
     */
    public function status_perubahans(): BelongsTo
    {
        return $this->belongsTo(StatusPerubahan::class, 'status_perubahan_id', 'id');
    }

    /**
     * Get the verifikator_1 that owns the RiwayatPerubahan
     *
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
     */
    public function verifikator_1_users(): BelongsTo
    {
        return $this->belongsTo(User::class, 'verifikator_1', 'id');
    }

    /**
     * Get all of the perubahan_keluargas for the RiwayatPerubahan
     *
     * @return \Illuminate\Database\Eloquent\Relations\HasMany
     */
    public function perubahan_keluargas(): HasMany
    {
        return $this->hasMany(PerubahanKeluarga::class, 'riwayat_perubahan_id', 'id');
    }

    /**
     * Get all of the perubahan_personals for the RiwayatPerubahan
     *
     * @return \Illuminate\Database\Eloquent\Relations\HasMany
     */
    public function perubahan_personals(): HasMany
    {
        return $this->hasMany(PerubahanPersonal::class, 'riwayat_perubahan_id', 'id');
    }
}
