<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class RelasiVerifikasi extends Model
{
    use HasFactory;

    protected $guarded = ['id'];
    protected $casts = [
        'verifikator' => 'integer',
        'modul_verifikasi' => 'integer',
        'order' => 'integer',
        'user_diverifikasi' => 'array'
    ];

    /**
     * Get the users that owns the RelasiVerfikasi
     *
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
     */
    public function users(): BelongsTo
    {
        return $this->belongsTo(User::class, 'verifikator', 'id');
    }

    /**
     * Get the modul_verifikasis that owns the RelasiVerfikasi
     *
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
     */
    public function modul_verifikasis(): BelongsTo
    {
        return $this->belongsTo(ModulVerifikasi::class, 'modul_verifikasi', 'id');
    }
}
