<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class StatusRiwayatIzin extends Model
{
    use HasFactory;
    protected $guarded = ['id'];

    protected $casts = [
        'id' => 'integer',
        'label' => 'string'
    ];

    /**
     * Get all of the riwayat_izins for the StatusRiwayatIzin
     *
     * @return \Illuminate\Database\Eloquent\Relations\HasMany
     */
    public function riwayat_izins(): HasMany
    {
        return $this->hasMany(RiwayatIzin::class, 'status_izin_id', 'id');
    }
}
