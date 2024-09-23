<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class KategoriTagihanPotongan extends Model
{
    use HasFactory;

    protected $guarded = ['id'];
    protected $casts = [
        'id' => 'integer',
        'label' => 'string'
    ];

    /**
     * Get all of the tagihan_potonga for the KategoriTagihanPotongan
     *
     * @return \Illuminate\Database\Eloquent\Relations\HasMany
     */
    public function tagihan_potongans(): HasMany
    {
        return $this->hasMany(TagihanPotongan::class, 'kategori_tagihan_id', 'id');
    }
}
