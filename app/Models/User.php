<?php

namespace App\Models;

// use Illuminate\Contracts\Auth\MustVerifyEmail;
use Laravel\Sanctum\HasApiTokens;
use Spatie\Permission\Traits\HasRoles;
use Illuminate\Notifications\Notifiable;
use Illuminate\Database\Eloquent\Relations\HasOne;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;

class User extends Authenticatable
{
    use HasApiTokens, HasFactory, Notifiable, HasRoles;

    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $guarded = ['id'];

    /**
     * The attributes that should be hidden for serialization.
     *
     * @var array<int, string>
     */
    protected $hidden = [
        'password',
        'remember_token',
    ];

    /**
     * The attributes that should be cast.
     *
     * @var array<string, string>
     */
    protected $casts = [
        'email_verified_at' => 'datetime',
        'password' => 'hashed',
    ];

    /**
     * Get the data_karyawan associated with the User
     *
     * @return \Illuminate\Database\Eloquent\Relations\HasOne
     */
    public function data_karyawans(): HasOne
    {
        return $this->hasOne(DataKaryawan::class, 'user_id', 'id');
    }

    /**
     * Get all of the cuti for the User
     *
     * @return \Illuminate\Database\Eloquent\Relations\HasMany
     */
    public function cutis(): HasMany
    {
        return $this->hasMany(Cuti::class, 'user_id', 'id');
    }

    /**
     * Get all of the presensi for the User
     *
     * @return \Illuminate\Database\Eloquent\Relations\HasMany
     */
    public function presensis(): HasMany
    {
        return $this->hasMany(Presensi::class, 'user_id', 'id');
    }

    /**
     * Get all of the jadwal for the User
     *
     * @return \Illuminate\Database\Eloquent\Relations\HasMany
     */
    public function jadwals(): HasMany
    {
        return $this->hasMany(Jadwal::class, 'user_id', 'id');
    }

    /**
     * Get all of the lembur for the User
     *
     * @return \Illuminate\Database\Eloquent\Relations\HasMany
     */
    public function lemburs(): HasMany
    {
        return $this->hasMany(Lembur::class, 'user_id', 'id');
    }

    /**
     * Get all of the activity_log for the User
     *
     * @return \Illuminate\Database\Eloquent\Relations\HasMany
     */
    public function activity_logs(): HasMany
    {
        return $this->hasMany(ActivityLog::class, 'user_id', 'id');
    }

    /**
     * Get all of the track_record for the User
     *
     * @return \Illuminate\Database\Eloquent\Relations\HasMany
     */
    public function track_records(): HasMany
    {
        return $this->hasMany(TrackRecord::class, 'user_id', 'id');
    }

    /**
     * Get all of the tranfer_karyawan for the User
     *
     * @return \Illuminate\Database\Eloquent\Relations\HasMany
     */
    public function tranfer_karyawans(): HasMany
    {
        return $this->hasMany(TransferKaryawan::class, 'user_id', 'id');
    }

    /**
     * Get all of the berkas for the User
     *
     * @return \Illuminate\Database\Eloquent\Relations\HasMany
     */
    public function berkas(): HasMany
    {
        return $this->hasMany(Berkas::class, 'user_id', 'id');
    }

    /**
     * Get all of the verifikator_1_userberkas for the User
     *
     * @return \Illuminate\Database\Eloquent\Relations\HasMany
     */
    public function verifikator_1_userberkas(): HasMany
    {
        return $this->hasMany(Berkas::class, 'verifikator_1', 'id');
    }

    /**
     * Get all of the verifikator_1 for the User
     *
     * @return \Illuminate\Database\Eloquent\Relations\HasMany
     */
    // public function verifikator_1_riwayatgajis(): HasMany
    // {
    //     return $this->hasMany(RiwayatPenggajian::class, 'verifikator_1', 'id');
    // }

    /**
     * Get all of the verifikator_2 for the User
     *
     * @return \Illuminate\Database\Eloquent\Relations\HasMany
     */
    // public function verifikator_2_riwayatgajis(): HasMany
    // {
    //     return $this->hasMany(RiwayatPenggajian::class, 'verifikator_2', 'id');
    // }

    /**
     * Get all of the notifikasis for the User
     *
     * @return \Illuminate\Database\Eloquent\Relations\HasMany
     */
    public function notifikasis(): HasMany
    {
        return $this->hasMany(Notifikasi::class, 'user_id', 'id');
    }

    /**
     * Get all of the user_pelapor for the User
     *
     * @return \Illuminate\Database\Eloquent\Relations\HasMany
     */
    public function user_pelapor(): HasMany
    {
        return $this->hasMany(Pelaporan::class, 'pelapor', 'id');
    }

    /**
     * Get all of the user_pelaku for the User
     *
     * @return \Illuminate\Database\Eloquent\Relations\HasMany
     */
    public function user_pelaku(): HasMany
    {
        return $this->hasMany(Pelaporan::class, 'pelaku', 'id');
    }

    /**
     * Get the status_aktif that owns the User
     *
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
     */
    public function status_aktif(): BelongsTo
    {
        return $this->belongsTo(StatusAktif::class, 'status_aktif', 'id');
    }

    /**
     * Get all of the peserta_diklat for the User
     *
     * @return \Illuminate\Database\Eloquent\Relations\HasMany
     */
    public function peserta_diklat(): HasMany
    {
        return $this->hasMany(PesertaDiklat::class, 'peserta', 'id');
    }

    /**
     * Get all of the verifikator_1 for the User
     *
     * @return \Illuminate\Database\Eloquent\Relations\HasMany
     */
    public function verifikator_1_riwayatperubahans(): HasMany
    {
        return $this->hasMany(RiwayatPerubahan::class, 'verifikator_1', 'id');
    }

    /**
     * Get all of the verifikator_1_statusaktifs for the User
     *
     * @return \Illuminate\Database\Eloquent\Relations\HasMany
     */
    public function verifikator_1_statusaktifs(): HasMany
    {
        return $this->hasMany(DataKaryawan::class, 'verifikator_1', 'id');
    }

    /**
     * Get all of the user_penilaian_dinilais for the User
     *
     * @return \Illuminate\Database\Eloquent\Relations\HasMany
     */
    public function user_penilaian_dinilais(): HasMany
    {
        return $this->hasMany(Penilaian::class, 'user_dinilai', 'id');
    }
}
