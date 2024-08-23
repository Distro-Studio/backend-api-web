<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class ActivityLog extends Model
{
    use HasFactory;
    protected $guarded = ['id'];

    protected $casts = [
        'id' => 'integer',
        'kategori_activity_id' => 'integer',
        'user_id' => 'integer',
    ];

    /**
     * Get the user that owns the ActivityLog
     *
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
     */
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class, 'user_id', 'id');
    }

    /**
     * Get the kategori_activity_logs that owns the ActivityLog
     *
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
     */
    public function kategori_activity_logs(): BelongsTo
    {
        return $this->belongsTo(KategoriActivityLog::class, 'kategori_activity_id', 'id');
    }
}
