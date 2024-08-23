<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class PengurangGaji extends Model
{
    use HasFactory;
    protected $guarded = ['id'];

    protected $casts = [
        'id' => 'integer',
        'data_karyawan_id' => 'integer',
        'premi_id' => 'integer'
    ];

    /**
     * Get the data_karyawans that owns the PengurangGaji
     *
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
     */
    public function data_karyawans(): BelongsTo
    {
        return $this->belongsTo(DataKaryawan::class, 'data_karyawan_id', 'id');
    }

    /**
     * Get the premis that owns the PengurangGaji
     *
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
     */
    public function premis(): BelongsTo
    {
        return $this->belongsTo(Premi::class, 'premi_id', 'id');
    }
}
