<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Spatie\Permission\Models\Role;

class Pertanyaan extends Model
{
    use HasFactory, SoftDeletes;
    protected $guarded = ['id'];

    protected $casts = [
        'id' => 'integer',
        'jenis_penilaian_id' => 'integer'
    ];

    /**
     * Get the jenis_penilaians that owns the Pertanyaan
     *
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
     */
    public function jenis_penilaians(): BelongsTo
    {
        return $this->belongsTo(JenisPenilaian::class, 'jenis_penilaian_id', 'id');
    }
}
