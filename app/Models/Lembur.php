<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\SoftDeletes;

class Lembur extends Model
{
    use HasFactory, SoftDeletes;
    protected $guarded = ['id'];

    protected $casts = [
        'id' => 'integer',
        'user_id' => 'integer',
        'jadwal_id' => 'integer',
        'durasi' => 'integer'
    ];

    /**
     * Get the user that owns the Lembur
     *
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
     */
    public function users(): BelongsTo
    {
        return $this->belongsTo(User::class, 'user_id', 'id');
    }

    /**
     * Get the jadwal that owns the Lembur
     *
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
     */
    public function jadwals(): BelongsTo
    {
        return $this->belongsTo(Jadwal::class, 'jadwal_id', 'id');
    }

    /**
     * Get the status_lemburs that owns the Lembur
     *
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
     */
    public function status_lemburs(): BelongsTo
    {
        return $this->belongsTo(StatusLembur::class, 'status_lembur_id', 'id');
    }

    /**
     * Get the kategori_kompensasis that owns the Lembur
     *
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
     */
    public function kategori_kompensasis(): BelongsTo
    {
        return $this->belongsTo(KategoriKompensasi::class, 'kompensasi_lembur_id', 'id');
    }
}
