<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class Shift extends Model
{
    use HasFactory, SoftDeletes;

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
}
