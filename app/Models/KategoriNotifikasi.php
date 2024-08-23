<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class KategoriNotifikasi extends Model
{
    use HasFactory;
    protected $guarded = ['id'];

    protected $casts = [
        'id' => 'integer',
        'label' => 'string'
    ];

    /**
     * Get all of the notifikasis for the KategoriNotifikasi
     *
     * @return \Illuminate\Database\Eloquent\Relations\HasMany
     */
    public function notifikasis(): HasMany
    {
        return $this->hasMany(Notifikasi::class, 'kategori_notifikasi_id', 'id');
    }
}
