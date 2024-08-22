<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class RiwayatIzin extends Model
{
    use HasFactory;

    protected $guarded = ['id'];

    /**
     * Get the users that owns the RiwayatIzin
     *
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
     */
    public function users(): BelongsTo
    {
        return $this->belongsTo(User::class, 'user_id', 'id');
    }
    
    /**
     * Get the status_izins that owns the RiwayatIzin
     *
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
     */
    public function status_izins(): BelongsTo
    {
        return $this->belongsTo(StatusRiwayatIzin::class, 'status_izin_id', 'id');
    }
}
