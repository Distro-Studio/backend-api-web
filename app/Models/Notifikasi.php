<?php

namespace App\Models;

use App\Models\User;
use App\Models\KategoriNotifikasi;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class Notifikasi extends Model
{
    use HasFactory;

    protected $guarded = ['id'];

    /**
     * Get the kategori_notifikasis that owns the Notifikasi
     *
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
     */
    public function kategori_notifikasis(): BelongsTo
    {
        return $this->belongsTo(KategoriNotifikasi::class, 'kategori_notifikasi_id', 'id');
    }

    /**
     * Get the users that owns the Notifikasi
     *
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
     */
    public function users(): BelongsTo
    {
        return $this->belongsTo(User::class, 'user_id', 'id');
    }
}
