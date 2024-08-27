<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class Penilaian extends Model
{
    use HasFactory;
    protected $guarded = ['id'];

    protected $casts = [
        'id' => 'integer',
        'user_dinilai' => 'integer',
        'user_penilai' => 'integer',
        'jenis_penilaian_id' => 'integer',
        'total_pertanyaan' => 'integer',
        'rata_rata' => 'integer',
        'pertanyaan_jawaban' => 'array',
    ];

    /**
     * Get the jenis_penilaians that owns the Penilaian
     *
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
     */
    public function jenis_penilaians(): BelongsTo
    {
        return $this->belongsTo(JenisPenilaian::class, 'jenis_penilaian_id', 'id');
    }

    /**
     * Get the user_dinilais that owns the Jawaban
     *
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
     */
    public function user_dinilais(): BelongsTo
    {
        return $this->belongsTo(User::class, 'user_dinilai', 'id');
    }

    /**
     * Get the user_penilais that owns the Jawaban
     *
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
     */
    public function user_penilais(): BelongsTo
    {
        return $this->belongsTo(User::class, 'user_penilai', 'id');
    }
}
