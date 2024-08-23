<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class StatusBerkas extends Model
{
    use HasFactory;
    protected $guarded = ['id'];

    protected $casts = [
        'id' => 'integer',
        'label' => 'string'
    ];

    /**
     * Get all of the berkas for the StatusBerkas
     *
     * @return \Illuminate\Database\Eloquent\Relations\HasMany
     */
    public function berkas(): HasMany
    {
        return $this->hasMany(Berkas::class, 'status_berkas_id', 'id');
    }
}
