<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\SoftDeletes;

class RiwayatIzin extends Model
{
    use HasFactory, SoftDeletes;
    protected $guarded = ['id'];

    protected $casts = [
        'id' => 'integer',
        'user_id' => 'integer',
        'durasi' => 'integer',
        'status_izin_id' => 'integer',
        'verifikator_1' => 'integer'
    ];

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

    /**
     * Get the verifikator_izins that owns the RiwayatIzin
     *
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
     */
    public function verifikator_izins(): BelongsTo
    {
        return $this->belongsTo(User::class, 'verifikator_1', 'id');
    }
}
