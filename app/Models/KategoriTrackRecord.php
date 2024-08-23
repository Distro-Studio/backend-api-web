<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class KategoriTrackRecord extends Model
{
    use HasFactory;
    protected $guarded = ['id'];

    protected $casts = [
        'id' => 'integer',
        'label' => 'string'
    ];

    /**
     * Get all of the track_records for the KategoriTrackRecord
     *
     * @return \Illuminate\Database\Eloquent\Relations\HasMany
     */
    public function track_records(): HasMany
    {
        return $this->hasMany(TrackRecord::class, 'kategori_record_id', 'id');
    }
}
