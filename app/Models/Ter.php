<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class Ter extends Model
{
    use HasFactory, SoftDeletes;
    protected $guarded = ['id'];

    protected $casts = [
        'id' => 'integer',
        'kategori_ter_id' => 'integer',
        'from_ter' => 'integer',
        'to_ter' => 'integer',
        'percentage' => 'decimal:4,2'
    ];

    /**
     * Get the kategori_ter that owns the Ter
     *
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
     */
    public function kategori_ters(): BelongsTo
    {
        return $this->belongsTo(KategoriTer::class, 'kategori_ter_id', 'id');
    }

    /**
     * Get the ptkps that owns the Ter
     *
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
     */
    public function ptkps(): BelongsTo
    {
        return $this->belongsTo(Ptkp::class, 'ptkp_id', 'id');
    }
}
