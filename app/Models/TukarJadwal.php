<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class TukarJadwal extends Model
{
    use HasFactory, SoftDeletes;
    protected $guarded = ['id'];

    protected $casts = [
        'id' => 'integer',
        'user_pengajuan' => 'integer',
        'jadwal_pengajuan' => 'integer',
        'user_ditukar' => 'integer',
        'jadwal_ditukar' => 'integer',
        'status_penukaran_id' => 'integer',
        'kategori_penukaran_id' => 'integer',
        'verifikator_1' => 'integer',
        'verifikator_2' => 'integer'
    ];

    /**
     * Get the user_pengajuan that owns the TukarJadwal
     *
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
     */
    public function user_pengajuans(): BelongsTo
    {
        return $this->belongsTo(User::class, 'user_pengajuan', 'id');
    }

    /**
     * Get the user_ditukar that owns the TukarJadwal
     *
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
     */
    public function user_ditukars(): BelongsTo
    {
        return $this->belongsTo(User::class, 'user_ditukar', 'id');
    }

    /**
     * Get the jadwal_pengajuan that owns the TukarJadwal
     *
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
     */
    public function jadwal_pengajuans(): BelongsTo
    {
        return $this->belongsTo(Jadwal::class, 'jadwal_pengajuan', 'id');
    }

    /**
     * Get the jadwal_ditukar that owns the TukarJadwal
     *
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
     */
    public function jadwal_ditukars(): BelongsTo
    {
        return $this->belongsTo(Jadwal::class, 'jadwal_ditukar', 'id');
    }

    /**
     * Get the status_tukar_jadwals that owns the TukarJadwal
     *
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
     */
    public function status_tukar_jadwals(): BelongsTo
    {
        return $this->belongsTo(StatusTukarJadwal::class, 'status_penukaran_id', 'id');
    }

    /**
     * Get the kategori_tukar_jadwals that owns the TukarJadwal
     *
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
     */
    public function kategori_tukar_jadwals(): BelongsTo
    {
        return $this->belongsTo(KategoriTukarJadwal::class, 'kategori_penukaran_id', 'id');
    }

    /**
     * Get the verifikator_1_users that owns the TukarJadwal
     *
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
     */
    public function verifikator_1_users(): BelongsTo
    {
        return $this->belongsTo(User::class, 'verifikator_1', 'id');
    }

    /**
     * Get the verifikator_2_admins that owns the TukarJadwal
     *
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
     */
    public function verifikator_2_admins(): BelongsTo
    {
        return $this->belongsTo(User::class, 'verifikator_2', 'id');
    }
}
