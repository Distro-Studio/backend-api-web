<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class TransferRelasiVerifikasi extends Model
{
    use HasFactory;

    protected $guarded = ['id'];

    protected $casts = [
        'id' => 'integer',
        'transfer_karyawan_id' => 'integer',
        'verifikator' => 'integer',
        'modul_verifikasi' => 'integer',
        'order' => 'integer',
        'user_diverifikasi' => 'array',
        'is_created' => 'boolean'
    ];

    /**
     * Get the transfer_karyawans that owns the TukarRelasiVerifikasi
     *
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
     */
    public function transfer_karyawans(): BelongsTo
    {
        return $this->belongsTo(TransferKaryawan::class, 'transfer_karyawan_id', 'id');
    }

    /**
     * Get the users that owns the TukarRelasiVerifikasi
     *
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
     */
    public function users(): BelongsTo
    {
        return $this->belongsTo(User::class, 'verifikator', 'id');
    }

    /**
     * Get the modul_verifikasis that owns the TukarRelasiVerifikasi
     *
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
     */
    public function modul_verifikasis(): BelongsTo
    {
        return $this->belongsTo(ModulVerifikasi::class, 'modul_verifikasi', 'id');
    }
}
