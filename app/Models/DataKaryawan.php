<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class DataKaryawan extends Model
{
    use HasFactory;
    protected $guarded = ['id'];

    protected $casts = [
        'id' => 'integer',
        'user_id' => 'integer',
        'unit_kerja_id' => 'integer',
        'jabatan_id' => 'integer',
        'kompetensi_id' => 'integer',
        'tunjangan_fungsional' => 'integer',
        'tunjangan_khusus' => 'integer',
        'tunjangan_lainnya' => 'integer',
        'uang_makan' => 'integer',
        'uang_lembur' => 'integer',
        'masa_kerja' => 'integer',
        'jenis_kelamin' => 'integer',
        'kategori_agama_id' => 'integer',
        'kategori_darah_id' => 'integer',
        'tinggi_badan' => 'integer',
        'berat_badan' => 'integer',
        'tahun_lulus' => 'integer',
        'status_karyawan_id' => 'integer',
        'kelompok_gaji_id' => 'integer',
        'ptkp_id' => 'integer',
        'masa_diklat' => 'integer',
        'verifikator_1' => 'integer',
        'status_reward_presensi' => 'integer',
        'pendidikan_terakhir' => 'integer',
        'bmi_value' => 'decimal:1',
    ];

    public function setUangMakan($value)
    {
        $this->attributes['uang_makan'] = $value == 0 ? null : $value;
    }

    public function setUangLembur($value)
    {
        $this->attributes['uang_lembur'] = $value == 0 ? null : $value;
    }

    public function setTunjanganJabatan($value)
    {
        $this->attributes['tunjangan_jabatan'] = $value == 0 ? null : $value;
    }

    public function setTunjanganFungsional($value)
    {
        $this->attributes['tunjangan_fungsional'] = $value == 0 ? null : $value;
    }

    public function setTunjanganKhusus($value)
    {
        $this->attributes['tunjangan_khusus'] = $value == 0 ? null : $value;
    }

    public function setTunjanganLainnya($value)
    {
        $this->attributes['tunjangan_lainnya'] = $value == 0 ? null : $value;
    }

    public function setMasaKerja($value)
    {
        $this->attributes['masa_kerja'] = $value == 0 ? null : $value;
    }

    public function setTahunLulus($value)
    {
        $this->attributes['tahun_lulus'] = $value == 0 ? null : $value;
    }

    public function setMasaDiklat($value)
    {
        $this->attributes['masa_diklat'] = $value == 0 ? null : $value;
    }

    /**
     * Get the user that owns the DataKaryawan
     *
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
     */
    public function users(): BelongsTo
    {
        return $this->belongsTo(User::class, 'user_id', 'id');
    }

    /**
     * Get the unit_kerja that owns the DataKaryawan
     *
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
     */
    public function unit_kerjas(): BelongsTo
    {
        return $this->belongsTo(UnitKerja::class, 'unit_kerja_id', 'id');
    }

    /**
     * Get the jabatan that owns the DataKaryawan
     *
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
     */
    public function jabatans(): BelongsTo
    {
        return $this->belongsTo(Jabatan::class, 'jabatan_id', 'id');
    }

    /**
     * Get the kompetensi that owns the DataKaryawan
     *
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
     */
    public function kompetensis(): BelongsTo
    {
        return $this->belongsTo(Kompetensi::class, 'kompetensi_id', 'id');
    }

    /**
     * Get the kelompok_gaji that owns the DataKaryawan
     *
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
     */
    public function kelompok_gajis(): BelongsTo
    {
        return $this->belongsTo(KelompokGaji::class, 'kelompok_gaji_id', 'id');
    }

    /**
     * Get the ptkp that owns the DataKaryawan
     *
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
     */
    public function ptkps(): BelongsTo
    {
        return $this->belongsTo(Ptkp::class, 'ptkp_id', 'id');
    }

    /**
     * Get all of the penggajian for the DataKaryawan
     *
     * @return \Illuminate\Database\Eloquent\Relations\HasMany
     */
    public function penggajians(): HasMany
    {
        return $this->hasMany(Penggajian::class, 'data_karyawan_id', 'id');
    }

    /**
     * Get the data_keluarga associated with the DataKaryawan
     *
     * @return \Illuminate\Database\Eloquent\Relations\HasMany
     */
    public function data_keluargas(): HasMany
    {
        return $this->hasMany(DataKeluarga::class, 'data_karyawan_id', 'id');
    }

    /**
     * Get all of the pengurang_gajis for the DataKaryawan
     *
     * @return \Illuminate\Database\Eloquent\Relations\HasMany
     */
    public function pengurang_gajis(): HasMany
    {
        return $this->hasMany(PengurangGaji::class, 'data_karyawan_id', 'id');
    }

    /**
     * Get all of the run_thrs for the DataKaryawan
     *
     * @return \Illuminate\Database\Eloquent\Relations\HasMany
     */
    public function run_thrs(): HasMany
    {
        return $this->hasMany(RunThr::class, 'data_karyawan_id', 'id');
    }

    /**
     * Get the kategori_agamas that owns the DataKaryawan
     *
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
     */
    public function kategori_agamas(): BelongsTo
    {
        return $this->belongsTo(KategoriAgama::class, 'kategori_agama_id', 'id');
    }

    /**
     * Get the status_karyawans that owns the DataKaryawan
     *
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
     */
    public function status_karyawans(): BelongsTo
    {
        return $this->belongsTo(StatusKaryawan::class, 'status_karyawan_id', 'id');
    }

    /**
     * Get the kategori_darahs that owns the DataKaryawan
     *
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
     */
    public function kategori_darahs(): BelongsTo
    {
        return $this->belongsTo(KategoriDarah::class, 'kategori_darah_id', 'id');
    }

    /**
     * Get the kategori_pendidikans that owns the DataKaryawan
     *
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
     */
    public function kategori_pendidikans(): BelongsTo
    {
        return $this->belongsTo(KategoriPendidikan::class, 'pendidikan_terakhir', 'id');
    }

    /**
     * Get all of the riwayat_perubahans for the DataKaryawan
     *
     * @return \Illuminate\Database\Eloquent\Relations\HasMany
     */
    public function riwayat_perubahans(): HasMany
    {
        return $this->hasMany(RiwayatPerubahan::class, 'data_karyawan_id', 'id');
    }

    /**
     * Get the verifikator_1_statusaktifs that owns the DataKaryawan
     *
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
     */
    public function verifikator_1_statusaktifs(): BelongsTo
    {
        return $this->belongsTo(User::class, 'verifikator_1', 'id');
    }

    /**
     * Get all of the reward_presensis for the DataKaryawan
     *
     * @return \Illuminate\Database\Eloquent\Relations\HasMany
     */
    public function reward_presensis(): HasMany
    {
        return $this->hasMany(RewardbulanLalu::class, 'data_karyawan_id', 'id');
    }

    /**
     * Get the file_ktp that owns the DataKaryawan
     *
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
     */
    public function file_ktp(): BelongsTo
    {
        return $this->belongsTo(Berkas::class, 'file_ktp', 'id');
    }

    /**
     * Get the file_kk that owns the DataKaryawan
     *
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
     */
    public function file_kk(): BelongsTo
    {
        return $this->belongsTo(Berkas::class, 'file_kk', 'id');
    }

    /**
     * Get the file_sip that owns the DataKaryawan
     *
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
     */
    public function file_sip(): BelongsTo
    {
        return $this->belongsTo(Berkas::class, 'file_sip', 'id');
    }

    /**
     * Get the file_bpjsksh that owns the DataKaryawan
     *
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
     */
    public function file_bpjsksh(): BelongsTo
    {
        return $this->belongsTo(Berkas::class, 'file_bpjsksh', 'id');
    }

    /**
     * Get the file_bpjsktk that owns the DataKaryawan
     *
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
     */
    public function file_bpjsktk(): BelongsTo
    {
        return $this->belongsTo(Berkas::class, 'file_bpjsktk', 'id');
    }

    /**
     * Get the file_ijazah that owns the DataKaryawan
     *
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
     */
    public function file_ijazah(): BelongsTo
    {
        return $this->belongsTo(Berkas::class, 'file_ijazah', 'id');
    }

    /**
     * Get the file_sertifikat that owns the DataKaryawan
     *
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
     */
    public function file_sertifikat(): BelongsTo
    {
        return $this->belongsTo(Berkas::class, 'file_sertifikat', 'id');
    }

    /**
     * Get all of the tagihan_potongans for the DataKaryawan
     *
     * @return \Illuminate\Database\Eloquent\Relations\HasMany
     */
    public function tagihan_potongans(): HasMany
    {
        return $this->hasMany(TagihanPotongan::class, 'data_karyawan_id', 'id');
    }
}
