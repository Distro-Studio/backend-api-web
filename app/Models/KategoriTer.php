<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\SoftDeletes;

class KategoriTer extends Model
{
    use HasFactory, SoftDeletes;
    protected $guarded = ['id'];

    protected $casts = [
        'id' => 'integer',
        'nama_kategori_ter' => 'string'
    ];

    /**
     * Get all of the ptkp for the KategoriTer
     *
     * @return \Illuminate\Database\Eloquent\Relations\HasMany
     */
    public function ptkps(): HasMany
    {
        return $this->hasMany(Ptkp::class, 'kategori_ter_id', 'id');
    }

    /**
     * Get all of the ter for the KategoriTer
     *
     * @return \Illuminate\Database\Eloquent\Relations\HasMany
     */
    public function ters(): HasMany
    {
        return $this->hasMany(Ter::class, 'kategori_ter_id', 'id');
    }
}
