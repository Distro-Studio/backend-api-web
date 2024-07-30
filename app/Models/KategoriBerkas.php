<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class KategoriBerkas extends Model
{
    use HasFactory;

    protected $guarded = ['id'];

    /**
     * Get all of the berkas for the KategoriBerkas
     *
     * @return \Illuminate\Database\Eloquent\Relations\HasMany
     */
    public function berkas(): HasMany
    {
        return $this->hasMany(Berkas::class, 'kategori_berkas_id', 'id');
    }
}
