<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class StatusCuti extends Model
{
    use HasFactory;
    protected $guarded = ['id'];

    protected $casts = [
        'id' => 'integer',
        'label' => 'string'
    ];

    /**
     * Get all of the cutis for the StatusCuti
     *
     * @return \Illuminate\Database\Eloquent\Relations\HasMany
     */
    public function cutis(): HasMany
    {
        return $this->hasMany(Cuti::class, 'status_cuti_id', 'id');
    }
}
