<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class StatusGaji extends Model
{
    use HasFactory;

    protected $guarded = ['id'];

    /**
     * Get all of the riwayat_penggajians for the StatusGaji
     *
     * @return \Illuminate\Database\Eloquent\Relations\HasMany
     */
    public function riwayat_penggajians(): HasMany
    {
        return $this->hasMany(RiwayatPenggajian::class, 'status_gaji_id', 'id');
    }

    /**
     * Get all of the penggajians for the StatusGaji
     *
     * @return \Illuminate\Database\Eloquent\Relations\HasMany
     */
    public function penggajians(): HasMany
    {
        return $this->hasMany(Penggajian::class, 'status_gaji_id', 'id');
    }
}
