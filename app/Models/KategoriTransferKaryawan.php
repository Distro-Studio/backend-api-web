<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class KategoriTransferKaryawan extends Model
{
    use HasFactory;
    protected $guarded = ['id'];

    protected $casts = [
        'id' => 'integer',
        'label' => 'string'
    ];

    /**
     * Get all of the transfer_karyawans for the KategoriTransferKaryawan
     *
     * @return \Illuminate\Database\Eloquent\Relations\HasMany
     */
    public function transfer_karyawans(): HasMany
    {
        return $this->hasMany(TransferKaryawan::class, 'kategori_transfer_id', 'id');
    }
}
