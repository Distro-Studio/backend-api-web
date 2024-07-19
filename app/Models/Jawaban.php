<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class Jawaban extends Model
{
    use HasFactory;

    protected $guarded = ['id'];

    /**
     * Get the pertanyaans that owns the Jawaban
     *
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
     */
    public function pertanyaans(): BelongsTo
    {
        return $this->belongsTo(Pertanyaan::class, 'pertanyaan_id', 'id');
    }

    /**
     * Get the penilaians that owns the Jawaban
     *
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
     */
    public function penilaians(): BelongsTo
    {
        return $this->belongsTo(Penilaian::class, 'penilaian_id', 'id');
    }
}
