<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class TukarJadwal extends Model
{
    use HasFactory;

    protected $guarded = ['id'];

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
}
