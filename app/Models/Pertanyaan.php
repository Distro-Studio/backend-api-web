<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class Pertanyaan extends Model
{
    use HasFactory, SoftDeletes;

    protected $guarded = ['id'];

    /**
     * Get the jabatans that owns the Pertanyaan
     *
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
     */
    public function jabatans(): BelongsTo
    {
        return $this->belongsTo(Jabatan::class, 'jabatan_id', 'id');
    }
}
