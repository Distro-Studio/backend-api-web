<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\SoftDeletes;

class Presensi extends Model
{
    use HasFactory, SoftDeletes;
    protected $guarded = ['id'];

    protected $casts = [
        'id' => 'integer',
        'user_id' => 'integer',
        'data_karyawan_id' => 'integer',
        'jadwal_id' => 'integer',
        'durasi' => 'integer',
        'foto_masuk' => 'integer',
        'foto_keluar' => 'integer',
        'kategori_presensi_id' => 'integer',
        'is_pembatalan_reward' => 'integer',
        'is_anulir_presensi' => 'integer',
    ];

    /**
     * Get the user that owns the Presensi
     *
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
     */
    public function users(): BelongsTo
    {
        return $this->belongsTo(User::class, 'user_id', 'id');
    }

    /**
     * Get the data_karyawans that owns the Presensi
     *
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
     */
    public function data_karyawans(): BelongsTo
    {
        return $this->belongsTo(DataKaryawan::class, 'data_karyawan_id', 'id');
    }

    /**
     * Get the jadwal that owns the Presensi
     *
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
     */
    public function jadwals(): BelongsTo
    {
        return $this->belongsTo(Jadwal::class, 'jadwal_id', 'id');
    }

    /**
     * Get the berkas that owns the Presensi
     *
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
     */
    public function berkas_foto_masuk(): BelongsTo
    {
        return $this->belongsTo(Berkas::class, 'foto_masuk', 'id');
    }

    /**
     * Get the berkas that owns the Presensi
     *
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
     */
    public function berkas_foto_keluar(): BelongsTo
    {
        return $this->belongsTo(Berkas::class, 'foto_masuk', 'id');
    }

    /**
     * Get the kategori_presensis that owns the Presensi
     *
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
     */
    public function kategori_presensis(): BelongsTo
    {
        return $this->belongsTo(KategoriPresensi::class, 'kategori_presensi_id', 'id');
    }
}
