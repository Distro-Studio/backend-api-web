<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class TemplateCertificate extends Model
{
    use HasFactory;

    protected $guarded = ['id'];
    protected $casts = [
        'id' => 'integer',
        'diklat_id' => 'integer'
    ];

    /**
     * Get the internal_diklat_templates that owns the TemplateCertificate
     *
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
     */
    public function internal_diklat_templates(): BelongsTo
    {
        return $this->belongsTo(Diklat::class, 'diklat_id', 'id');
    }
}
