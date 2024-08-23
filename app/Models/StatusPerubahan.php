<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class StatusPerubahan extends Model
{
    use HasFactory;
    protected $guarded = ['id'];

    protected $casts = [
        'id' => 'integer',
        'label' => 'string'
    ];

    /**
     * Get all of the riwayat_perubahans for the StatusPerubahan
     *
     * @return \Illuminate\Database\Eloquent\Relations\HasMany
     */
    public function riwayat_perubahans(): HasMany
    {
        return $this->hasMany(RiwayatPerubahan::class, 'status_perubahan_id', 'id');
    }
}
