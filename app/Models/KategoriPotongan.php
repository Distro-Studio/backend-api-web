<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class KategoriPotongan extends Model
{
    use HasFactory;

    protected $guarded = ['id'];

    /**
     * Get all of the premis for the KategoriPotongan
     *
     * @return \Illuminate\Database\Eloquent\Relations\HasMany
     */
    public function premis(): HasMany
    {
        return $this->hasMany(Premi::class, 'kategori_potongan_id', 'id');
    }
}
