<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class TagihanPotongan extends Model
{
    use HasFactory;

    protected $guarded = ['id'];
    protected $casts = [
        'id' => 'integer',
        'data_karyawan_id' => 'integer',
        'kategori_tagihan_id' => 'integer',
        'status_tagihan_id' => 'integer',
        'min_tagihan' => 'integer',
        'besaran' => 'integer',
        'tenor' => 'integer',
        'sisa_tenor' => 'integer',
        'sisa_tagihan' => 'integer',
        'is_pelunasan' => 'integer'
    ];

    /**
     * Get the tagihan_karyawans that owns the TagihanPotongan
     *
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
     */
    public function tagihan_karyawans(): BelongsTo
    {
        return $this->belongsTo(DataKaryawan::class, 'data_karyawan_id', 'id');
    }

    /**
     * Get the tagihan_kategoris that owns the TagihanPotongan
     *
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
     */
    public function tagihan_kategoris(): BelongsTo
    {
        return $this->belongsTo(KategoriTagihanPotongan::class, 'kategori_tagihan_id', 'id');
    }

    /**
     * Get the tagihan_status that owns the TagihanPotongan
     *
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
     */
    public function tagihan_status(): BelongsTo
    {
        return $this->belongsTo(StatusTagihanPotongan::class, 'status_tagihan_id', 'id');
    }

    /**
     * Get the user_verifikator that owns the TagihanPotongan
     *
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
     */
    public function user_verifikator(): BelongsTo
    {
        return $this->belongsTo(User::class, 'is_pelunasan', 'id');
    }
}
