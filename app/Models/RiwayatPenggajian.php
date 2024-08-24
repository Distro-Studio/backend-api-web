<?php

namespace App\Models;

use App\Models\Penggajian;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class RiwayatPenggajian extends Model
{
    use HasFactory;
    protected $guarded = ['id'];

    protected $casts = [
        'id' => 'integer',
        'karyawan_verifikasi' => 'integer',
        'jenis_riwayat' => 'integer',
        'status_gaji_id' => 'integer'
    ];

    /**
     * Get all of the penggajians for the RiwayatPenggajian
     *
     * @return \Illuminate\Database\Eloquent\Relations\HasMany
     */
    public function penggajians(): HasMany
    {
        return $this->hasMany(Penggajian::class, 'riwayat_penggajian_id', 'id');
    }

    // /**
    //  * Get the verifikator_1 that owns the RiwayatPenggajian
    //  *
    //  * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
    //  */
    // public function verifikator_1(): BelongsTo
    // {
    //     return $this->belongsTo(User::class, 'verifikator_1', 'id');
    // }

    // /**
    //  * Get the verifikator_2 that owns the RiwayatPenggajian
    //  *
    //  * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
    //  */
    // public function verifikator_2(): BelongsTo
    // {
    //     return $this->belongsTo(User::class, 'verifikator_2', 'id');
    // }

    /**
     * Get the status_gajis that owns the RiwayatPenggajian
     *
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
     */
    public function status_gajis(): BelongsTo
    {
        return $this->belongsTo(StatusGaji::class, 'status_gaji_id', 'id');
    }
}
