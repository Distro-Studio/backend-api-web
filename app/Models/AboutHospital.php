<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class AboutHospital extends Model
{
    use HasFactory;

    protected $guarded = ['id'];

    protected $casts = [
        'id' => 'integer',
        'edited_by' => 'integer',
        'about_hospital_1' => 'integer',
        'about_hospital_2' => 'integer',
        'about_hospital_3' => 'integer',
    ];

    /**
     * Get the user_edited that owns the AboutHospital
     *
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
     */
    public function user_edited(): BelongsTo
    {
        return $this->belongsTo(User::class, 'edited_by', 'id');
    }

        /**
     * Get the materi_berkas that owns the MateriPelatihan
     *
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
     */
    public function gambar_1_dokumen(): BelongsTo
    {
        return $this->belongsTo(Berkas::class, 'about_hospital_1', 'id');
    }

    public function gambar_2_dokumen(): BelongsTo
    {
        return $this->belongsTo(Berkas::class, 'about_hospital_2', 'id');
    }

    public function gambar_3_dokumen(): BelongsTo
    {
        return $this->belongsTo(Berkas::class, 'about_hospital_3', 'id');
    }
}
