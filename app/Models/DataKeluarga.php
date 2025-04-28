<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class DataKeluarga extends Model
{
    use HasFactory;
    protected $guarded = ['id'];

    protected $casts = [
        'id' => 'integer',
        'data_karyawan_id' => 'integer',
        'status_hidup' => 'integer',
        'status_keluarga_id' => 'integer',
        'is_bpjs' => 'integer',
        'verifikator_1' => 'integer',
        // 'pendidikan_terakhir' => 'integer',
        // 'jenis_kelamin' => 'integer',
        // 'kategori_agama_id' => 'integer',
        // 'kategori_darah_id' => 'integer',
    ];

    /**
     * Get the data_karyawan that owns the DataKeluarga
     *
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
     */
    public function data_karyawans(): BelongsTo
    {
        return $this->belongsTo(DataKaryawan::class, 'data_karyawan_id', 'id');
    }

    /**
     * Get all of the perubahan_keluargas for the DataKeluarga
     *
     * @return \Illuminate\Database\Eloquent\Relations\HasMany
     */
    public function perubahan_keluargas(): HasMany
    {
        return $this->hasMany(PerubahanKeluarga::class, 'data_keluarga_id', 'id');
    }

    /**
     * Get the status_keluargas that owns the DataKeluarga
     *
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
     */
    public function status_keluargas(): BelongsTo
    {
        return $this->belongsTo(StatusKeluarga::class, 'status_keluarga_id', 'id');
    }

    /**
     * Get the verifikator_1_users that owns the DataKeluarga
     *
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
     */
    public function verifikator_1_users(): BelongsTo
    {
        return $this->belongsTo(User::class, 'verifikator_1', 'id');
    }

    /**
     * Get the kategori_pendidikans that owns the DataKeluarga
     *
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
     */
    public function kategori_pendidikans(): BelongsTo
    {
        return $this->belongsTo(KategoriPendidikan::class, 'pendidikan_terakhir', 'id');
    }

    // /**
    //  * Get the kategori_agamas that owns the DataKaryawan
    //  *
    //  * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
    //  */
    // public function kategori_agamas(): BelongsTo
    // {
    //     return $this->belongsTo(KategoriAgama::class, 'kategori_agama_id', 'id');
    // }

    // /**
    //  * Get the kategori_darahs that owns the DataKaryawan
    //  *
    //  * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
    //  */
    // public function kategori_darahs(): BelongsTo
    // {
    //     return $this->belongsTo(KategoriDarah::class, 'kategori_darah_id', 'id');
    // }
}
