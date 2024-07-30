<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class Pelaporan extends Model
{
    use HasFactory;

    protected $guarded = ['id'];

    /**
     * Get the user_pelapor that owns the Pelaporan
     *
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
     */
    public function user_pelapor(): BelongsTo
    {
        return $this->belongsTo(User::class, 'pelapor', 'id');
    }

    /**
     * Get the user_pelaku that owns the Pelaporan
     *
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
     */
    public function user_pelaku(): BelongsTo
    {
        return $this->belongsTo(User::class, 'pelaku', 'id');
    }
}
