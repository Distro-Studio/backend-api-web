<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class RewardbulanLalu extends Model
{
    use HasFactory;

    protected $guarded = ['id'];

    protected $table = 'reward_bulan_lalus';

    /**
     * Get the data_karyawans that owns the RewardbulanLalu
     *
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
     */
    public function data_karyawans(): BelongsTo
    {
        return $this->belongsTo(DataKaryawan::class, 'data_karyawan_id', 'id');
    }
}
