<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class StatusLembur extends Model
{
    use HasFactory;
    protected $guarded = ['id'];

    protected $casts = [
        'id' => 'integer',
        'label' => 'string'
    ];

    /**
     * Get all of the lemburs for the StatusLembur
     *
     * @return \Illuminate\Database\Eloquent\Relations\HasMany
     */
    public function lemburs(): HasMany
    {
        return $this->hasMany(Lembur::class, 'status_lembur_id', 'id');
    }
}
