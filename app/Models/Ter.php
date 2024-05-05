<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class Ter extends Model
{
    use HasFactory;

    protected $guarded = ['id'];

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
