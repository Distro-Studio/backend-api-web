<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class StatusKeluarga extends Model
{
    use HasFactory;

    protected $guarded = ['id'];
    protected $casts = [
        'id' => 'integer',
        'label' => 'string'
    ];

    /**
     * Get all of the data_keluargas for the StatusKeluarga
     *
     * @return \Illuminate\Database\Eloquent\Relations\HasMany
     */
    public function data_keluargas(): HasMany
    {
        return $this->hasMany(DataKeluarga::class, 'status_keluarga_id', 'id');
    }
}
