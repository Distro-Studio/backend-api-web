<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class PesertaDiklat extends Model
{
    use HasFactory;

    protected $guarded = ['id'];

    /**
     * Get the diklats that owns the PesertaDiklat
     *
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
     */
    public function diklats(): BelongsTo
    {
        return $this->belongsTo(Diklat::class, 'diklat_id', 'id');
    }

    /**
     * Get the users that owns the PesertaDiklat
     *
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
     */
    public function users(): BelongsTo
    {
        return $this->belongsTo(User::class, 'peserta', 'id');
    }
}
