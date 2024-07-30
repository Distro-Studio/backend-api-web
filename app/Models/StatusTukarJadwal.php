<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class StatusTukarJadwal extends Model
{
    use HasFactory;

    protected $guarded = ['id'];

    /**
     * Get all of the tukar_jadwals for the StatusTukarJadwal
     *
     * @return \Illuminate\Database\Eloquent\Relations\HasMany
     */
    public function tukar_jadwals(): HasMany
    {
        return $this->hasMany(TukarJadwal::class, 'status_penukaran_id', 'id');
    }
}
