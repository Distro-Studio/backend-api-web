<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class RunThr extends Model
{
    use HasFactory;

    protected $guarded = ['id'];

    /**
     * Get the thr that owns the RunThr
     *
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
     */
    public function thr(): BelongsTo
    {
        return $this->belongsTo(Thr::class, 'thr_id', 'id');
    }

    /**
     * Get the user that owns the RunThr
     *
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
     */
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class, 'user_id', 'id');
    }
}
