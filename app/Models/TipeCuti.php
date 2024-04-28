<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class TipeCuti extends Model
{
    use HasFactory;

    protected $guarded = ['id'];

    /**
     * Get all of the cuti for the TipeCuti
     *
     * @return \Illuminate\Database\Eloquent\Relations\HasMany
     */
    public function cuti(): HasMany
    {
        return $this->hasMany(Cuti::class, 'tipe_cuti_id', 'id');
    }
}
