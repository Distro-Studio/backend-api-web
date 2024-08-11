<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class RiwayatPerubahan extends Model
{
    use HasFactory;

    protected $guarded = ['id'];

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
}
