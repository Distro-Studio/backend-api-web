<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class StatusTagihanPotongan extends Model
{
    use HasFactory;

    protected $guarded = ['id'];
    protected $casts = [
        'id' => 'integer',
        'label' => 'string'
    ];

    /**
     * Get all of the tagihan_potongans for the StatusTagihanPotongan
     *
     * @return \Illuminate\Database\Eloquent\Relations\HasMany
     */
    public function tagihan_potongans(): HasMany
    {
        return $this->hasMany(TagihanPotongan::class, 'status_tagihan_id', 'id');
    }
}
