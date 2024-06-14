<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class Shift extends Model
{
    use HasFactory;

    protected $guarded = ['id'];

    /**
     * Get all of the jadwal for the Shift
     *
     * @return \Illuminate\Database\Eloquent\Relations\HasMany
     */
    public function jadwals(): HasMany
    {
        return $this->hasMany(Jadwal::class, 'shift_id', 'id');
    }

    /**
     * Get all of the lembur for the Shift
     *
     * @return \Illuminate\Database\Eloquent\Relations\HasMany
     */
    public function lemburs(): HasMany
    {
        return $this->hasMany(Lembur::class, 'shift_id', 'id');
    }
}
