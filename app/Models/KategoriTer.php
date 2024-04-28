<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class KategoriTer extends Model
{
    use HasFactory;

    protected $guarded = ['id'];

    /**
     * Get all of the ptkp for the KategoriTer
     *
     * @return \Illuminate\Database\Eloquent\Relations\HasMany
     */
    public function ptkp(): HasMany
    {
        return $this->hasMany(Ptkp::class, 'kategori_ter_id', 'id');
    }

    /**
     * Get all of the ter for the KategoriTer
     *
     * @return \Illuminate\Database\Eloquent\Relations\HasMany
     */
    public function ter(): HasMany
    {
        return $this->hasMany(Ter::class, 'kategori_ter_id', 'id');
    }
}
