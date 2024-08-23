<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class Cuti extends Model
{
    use HasFactory;
    protected $guarded = ['id'];

    protected $casts = [
        'id' => 'integer',
        'user_id' => 'integer',
        'tipe_cuti_id' => 'integer',
        'durasi' => 'integer',
        'status_cuti_id' => 'integer',
        'verifikator_1' => 'integer',
        'verifikator_2' => 'integer',
    ];

    /**
     * Get the user that owns the Cuti
     *
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
     */
    public function users(): BelongsTo
    {
        return $this->belongsTo(User::class, 'user_id', 'id');
    }

    /**
     * Get the tipe_cuti that owns the Cuti
     *
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
     */
    public function tipe_cutis(): BelongsTo
    {
        return $this->belongsTo(TipeCuti::class, 'tipe_cuti_id', 'id');
    }

    /**
     * Get the status_cutis that owns the Cuti
     *
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
     */
    public function status_cutis(): BelongsTo
    {
        return $this->belongsTo(StatusCuti::class, 'status_cuti_id', 'id');
    }

    /**
     * Get the verifikator_1_cutis that owns the Cuti
     *
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
     */
    public function verifikator_1_cutis(): BelongsTo
    {
        return $this->belongsTo(User::class, 'verifikator_1', 'id');
    }

    /**
     * Get the verifikator_2_cutis that owns the Cuti
     *
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
     */
    public function verifikator_2_cutis(): BelongsTo
    {
        return $this->belongsTo(User::class, 'verifikator_2', 'id');
    }
}
