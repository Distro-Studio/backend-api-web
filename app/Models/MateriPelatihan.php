<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class MateriPelatihan extends Model
{
    use HasFactory;

    protected $guarded = ['id'];
    protected $casts = [
        'id' => 'integer',
        'pj_materi' => 'integer',
        'dokumen_materi_1' => 'integer',
        'dokumen_materi_2' => 'integer',
        'dokumen_materi_3' => 'integer',
    ];

    /**
     * Get the created_users that owns the MateriPelatihan
     *
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
     */
    public function created_users(): BelongsTo
    {
        return $this->belongsTo(User::class, 'pj_materi', 'id');
    }

    /**
     * Get the materi_berkas that owns the MateriPelatihan
     *
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
     */
    public function materi_1_berkas(): BelongsTo
    {
        return $this->belongsTo(Berkas::class, 'dokumen_materi_1', 'id');
    }

    public function materi_2_berkas(): BelongsTo
    {
        return $this->belongsTo(Berkas::class, 'dokumen_materi_2', 'id');
    }

    public function materi_3_berkas(): BelongsTo
    {
        return $this->belongsTo(Berkas::class, 'dokumen_materi_3', 'id');
    }
}
