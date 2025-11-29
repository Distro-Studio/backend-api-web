<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class Cuti extends Model
{
    use HasFactory;
    protected $guarded = ['id'];

    protected $casts = [
        'id' => 'integer',
        'user_id' => 'integer',
        'tipe_cuti_id' => 'integer',
        'hak_cuti_id' => 'integer',
        'durasi' => 'integer',
        'status_cuti_id' => 'integer',
        'sisa_kuota' => 'integer',
        'verifikator_1' => 'integer',
        'verifikator_2' => 'integer',
        'presensi_id' => 'integer',
        'jadwal_id' => 'integer',
        'tukar_jadwal_id' => 'integer',
        'izin_id' => 'integer',
        'lembur_id' => 'integer',
    ];

    /**
     * Get the user that owns the Cuti
     *
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
     */
    public function users(): BelongsTo
    {
        return $this->belongsTo(User::class, 'user_id', 'id');
    }

    /**
     * Get the tipe_cuti that owns the Cuti
     *
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
     */
    public function tipe_cutis(): BelongsTo
    {
        return $this->belongsTo(TipeCuti::class, 'tipe_cuti_id', 'id');
    }

    /**
     * Get the hak_cutis that owns the Cuti
     *
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
     */
    public function hak_cutis(): BelongsTo
    {
        return $this->belongsTo(HakCuti::class, 'hak_cuti_id', 'id');
    }

    /**
     * Get the status_cutis that owns the Cuti
     *
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
     */
    public function status_cutis(): BelongsTo
    {
        return $this->belongsTo(StatusCuti::class, 'status_cuti_id', 'id');
    }

    /**
     * Get the verifikator_1_cutis that owns the Cuti
     *
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
     */
    public function verifikator_1_cutis(): BelongsTo
    {
        return $this->belongsTo(User::class, 'verifikator_1', 'id');
    }

    /**
     * Get the verifikator_2_cutis that owns the Cuti
     *
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
     */
    public function verifikator_2_cutis(): BelongsTo
    {
        return $this->belongsTo(User::class, 'verifikator_2', 'id');
    }

    /**
     * Get the presensis that owns the Cuti
     *
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
     */
    public function presensis(): BelongsTo
    {
        return $this->belongsTo(Presensi::class, 'presensi_id', 'id');
    }

    /**
     * Get the jadwals that owns the Cuti
     *
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
     */
    public function jadwals(): BelongsTo
    {
        return $this->belongsTo(Jadwal::class, 'jadwal_id', 'id');
    }

    /**
     * Get the tukar_jadwals that owns the Cuti
     *
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
     */
    public function tukar_jadwals(): BelongsTo
    {
        return $this->belongsTo(TukarJadwal::class, 'tukar_jadwal_id', 'id');
    }

    /**
     * Get the riwayat_izins that owns the Cuti
     *
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
     */
    public function riwayat_izins(): BelongsTo
    {
        return $this->belongsTo(RiwayatIzin::class, 'izin_id', 'id');
    }

    /**
     * Get the lemburs that owns the Cuti
     *
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
     */
    public function lemburs(): BelongsTo
    {
        return $this->belongsTo(Lembur::class, 'lembur_id', 'id');
    }
}
