<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class TrackRecord extends Model
{
    use HasFactory;
    protected $guarded = ['id'];

    protected $casts = [
        'id' => 'integer',
        'user_id' => 'integer',
        'kategori_record_id' => 'integer'
    ];

    /**
     * Get the user that owns the TrackRecord
     *
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
     */
    public function users(): BelongsTo
    {
        return $this->belongsTo(User::class, 'user_id', 'id');
    }

    /**
     * Get the kategori_track_records that owns the TrackRecord
     *
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
     */
    public function kategori_track_records(): BelongsTo
    {
        return $this->belongsTo(KategoriTrackRecord::class, 'kategori_record_id', 'id');
    }
}
