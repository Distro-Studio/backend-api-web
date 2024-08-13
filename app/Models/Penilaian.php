<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class Penilaian extends Model
{
    use HasFactory;

    protected $guarded = ['id'];

    /**
     * Get the status_karyawans that owns the Penilaian
     *
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
     */
    public function status_karyawans(): BelongsTo
    {
        return $this->belongsTo(StatusKaryawan::class, 'status_karyawan_id', 'id');
    }

    /**
     * Get all of the pertanyaans for the Penilaian
     *
     * @return \Illuminate\Database\Eloquent\Relations\HasMany
     */
    public function pertanyaans(): HasMany
    {
        return $this->hasMany(Pertanyaan::class, 'penilaian_id', 'id');
    }

    // Method untuk menghitung total pertanyaan
    public function getTotalPertanyaanAttribute()
    {
        return $this->pertanyaans()->count();
    }

    public function jawabans()
    {
        return $this->hasManyThrough(Jawaban::class, Pertanyaan::class, 'penilaian_id', 'pertanyaan_id');
    }

    // Relasi untuk mengakses karyawan yang dinilai
    public function karyawanDinilai()
    {
        return $this->hasOneThrough(User::class, DataKaryawan::class, 'status_karyawan_id', 'id', 'status_karyawan_id', 'user_id');
    }

    // Method untuk menghitung rata-rata jawaban berdasarkan status karyawan
    public function getRataRataAttribute()
    {
        $average = $this->jawabans()
            ->whereHas('pertanyaans.penilaians.status_karyawans', function ($query) {
                $query->where('id', $this->status_karyawan_id);
            })
            ->avg('jawaban');
        return round($average);
    }
}
