<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class RiwayatThr extends Model
{
    use HasFactory;
    protected $guarded = ['id'];

    protected $casts = [
        'id' => 'integer',
        'riwayat_penggajian_id' => 'integer',
        'karyawan_thr' => 'integer',
        'created_by' => 'integer',
        'updated_by' => 'integer',
    ];

    /**
     * Get the riwayat_penggajians that owns the Penggajian
     *
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
     */
    public function riwayat_penggajians(): BelongsTo
    {
        return $this->belongsTo(RiwayatPenggajian::class, 'riwayat_penggajian_id', 'id');
    }

    /**
     * Get the created_users that owns the Penggajian
     *
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
     */
    public function created_users(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by', 'id');
    }

    /**
     * Get the updated_users that owns the RiwayatThr
     *
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
     */
    public function updated_users(): BelongsTo
    {
        return $this->belongsTo(User::class, 'updated_by', 'id');
    }
}
