<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasOne;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class Thr extends Model
{
    use HasFactory;
    protected $guarded = ['id'];

    protected $casts = [
        'id' => 'integer',
        'nominal_satu' => 'integer',
        'nominal_dua' => 'integer'
    ];

    /**
     * Get the run_thr associated with the Thr
     *
     * @return \Illuminate\Database\Eloquent\Relations\HasOne
     */
    public function run_thr(): HasOne
    {
        return $this->hasOne(RunThr::class, 'thr_id', 'id');
    }
}
