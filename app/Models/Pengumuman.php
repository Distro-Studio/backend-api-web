<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class Pengumuman extends Model
{
    use HasFactory;
    protected $guarded = ['id'];
    protected $table = 'pengumumans';

    protected $casts = [
        'id' => 'integer',
        'user_id' => 'integer',
        'is_read' => 'integer'
    ];

    /**
     * Get the users that owns the Pengumuman
     *
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
     */
    public function users(): BelongsTo
    {
        return $this->belongsTo(User::class, 'user_id', 'id');
    }
}
