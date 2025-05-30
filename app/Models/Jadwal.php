<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class Jadwal extends Model
{
    use HasFactory, SoftDeletes;
    protected $guarded = ['id'];
    protected $table = 'jadwals';

    protected $casts = [
        'id' => 'integer',
        'user_id' => 'integer',
        'shift_id' => 'integer',
        'ex_libur' => 'integer',
    ];

    /**
     * Get all of the presensi for the Jadwal
     *
     * @return \Illuminate\Database\Eloquent\Relations\HasMany
     */
    public function presensis(): HasMany
    {
        return $this->hasMany(Presensi::class, 'jadwal_id', 'id');
    }

    /**
     * Get the user that owns the Jadwal
     *
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
     */
    public function users(): BelongsTo
    {
        return $this->belongsTo(User::class, 'user_id', 'id');
    }

    /**
     * Get the shift that owns the Jadwal
     *
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
     */
    public function shifts(): BelongsTo
    {
        return $this->belongsTo(Shift::class, 'shift_id', 'id')->withTrashed();
    }

    /**
     * Get all of the lemburs for the Jadwal
     *
     * @return \Illuminate\Database\Eloquent\Relations\HasMany
     */
    public function lemburs(): HasMany
    {
        return $this->hasMany(Lembur::class, 'jadwal_id', 'id');
    }
}
