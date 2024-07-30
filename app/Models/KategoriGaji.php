<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class KategoriGaji extends Model
{
    use HasFactory;

    protected $guarded = ['id'];

    /**
     * Get all of the detail_gajis for the KategoriGaji
     *
     * @return \Illuminate\Database\Eloquent\Relations\HasMany
     */
    public function detail_gajis(): HasMany
    {
        return $this->hasMany(DetailGaji::class, 'kategori_gaji_id', 'id');
    }

    /**
     * Get all of the penyesuaian_gajis for the KategoriGaji
     *
     * @return \Illuminate\Database\Eloquent\Relations\HasMany
     */
    public function penyesuaian_gajis(): HasMany
    {
        return $this->hasMany(PenyesuaianGaji::class, 'kategori_gaji_id', 'id');
    }
}
