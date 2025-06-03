<?php

namespace App\Http\Resources\Dashboard\Karyawan;

use App\Models\UnitKerja;
use Illuminate\Http\Request;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;
use Illuminate\Http\Resources\Json\JsonResource;

class KaryawanResource extends JsonResource
{
    public $status;
    public $message;
    public $data;

    public function __construct($status, $message, $data)
    {
        parent::__construct($data);
        $this->status = $status;
        $this->message = $message;
    }

    public function toArray($request)
    {
        // Check if the resource is a paginator instance and adapt the response accordingly
        if ($this->resource instanceof \Illuminate\Pagination\LengthAwarePaginator) {
            return [
                'status' =>  $this->status,
                'message' => $this->message,
                'data' => $this->formatData($this->resource->getCollection()),
                'links' => [
                    'first' => $this->resource->url(1),
                    'last' => $this->resource->url($this->resource->lastPage()),
                    'prev' => $this->resource->previousPageUrl(),
                    'next' => $this->resource->nextPageUrl(),
                ],
                'meta' => [
                    'current_page' => $this->resource->currentPage(),
                    'from' => $this->resource->firstItem(),
                    'last_page' => $this->resource->lastPage(),
                    'per_page' => $this->resource->perPage(),
                    'to' => $this->resource->lastItem(),
                    'total' => $this->resource->total(),
                ],
            ];
        } else {
            return [
                'status' =>  $this->status,
                'message' => $this->message,
                'data' => $this->formatData(collect([$this->resource])),
            ];
        }
    }


    protected function formatData(Collection $collection)
    {
        return $collection->transform(function ($karyawan) {
            $role = $karyawan->users->roles->first(); // Mengambil role pertama jika ada

            $pjUnitKerjaValue = $karyawan->pj_unit_kerja;

            if (is_array($pjUnitKerjaValue)) {
                $pjUnitKerjaArray = array_map('intval', $pjUnitKerjaValue);
            } elseif (is_string($pjUnitKerjaValue)) {
                $pjUnitKerjaArray = explode(',', trim($pjUnitKerjaValue, '[]'));
                $pjUnitKerjaArray = array_map('intval', $pjUnitKerjaArray);
            } else {
                $pjUnitKerjaArray = [];
            }

            // Query data lengkap unit kerja
            $unitKerjaList = [];
            if (!empty($pjUnitKerjaArray)) {
                $unitKerjaList = UnitKerja::whereIn('id', $pjUnitKerjaArray)->get();
            }

            return [
                'id' => $karyawan->id,
                'user' => [
                    'id' => $karyawan->users->id,
                    'nama' => $karyawan->users->nama,
                    'username' => $karyawan->users->username,
                    'email_verified_at' => $karyawan->users->email_verified_at,
                    'data_karyawan_id' => $karyawan->users->data_karyawan_id,
                    'foto_profil' => $karyawan->users->foto_profiles ? [
                        'id' => $karyawan->users->foto_profiles->id,
                        'user_id' => $karyawan->users->foto_profiles->user_id,
                        'file_id' => $karyawan->users->foto_profiles->file_id,
                        'nama' => $karyawan->users->foto_profiles->nama,
                        'nama_file' => $karyawan->users->foto_profiles->nama_file,
                        'path' => env('STORAGE_SERVER_DOMAIN') . $karyawan->users->foto_profiles->path,
                        'ext' => $karyawan->users->foto_profiles->ext,
                        'size' => $karyawan->users->foto_profiles->size,
                    ] : null,
                    'data_completion_step' => $karyawan->users->data_completion_step,
                    'status_aktif' => $karyawan->users->status_aktif,
                    'created_at' => $karyawan->users->created_at,
                    'updated_at' => $karyawan->users->updated_at
                ],
                'role' => [
                    'id' => $role ? $role->id : null,
                    'name' => $role ? $role->name : null,
                    'deskripsi' => $role ? $role->deskripsi : null,
                    'created_at' => $role ? $role->created_at : null,
                    'updated_at' => $role ? $role->updated_at : null
                ],
                'potongan_gaji' => DB::table('pengurang_gajis')
                    ->join('premis', 'pengurang_gajis.premi_id', '=', 'premis.id')
                    ->where('pengurang_gajis.data_karyawan_id', $karyawan->id)
                    ->whereNull('pengurang_gajis.deleted_at')
                    ->select(
                        'premis.id',
                        'premis.nama_premi',
                        'premis.kategori_potongan_id',
                        'premis.jenis_premi',
                        'premis.besaran_premi',
                        'premis.minimal_rate',
                        'premis.maksimal_rate',
                        'premis.created_at',
                        'premis.updated_at'
                    )
                    ->get(),
                'email' => $karyawan->email,
                'nik' => $karyawan->nik,
                'pj_unit_kerja' => $unitKerjaList,
                'no_rm' => $karyawan->no_rm,
                'no_sip' => $karyawan->no_sip,
                'created_sip' => $karyawan->created_sip,
                'masa_berlaku_sip' => $karyawan->masa_berlaku_sip,
                'no_manulife' => $karyawan->no_manulife,
                'tgl_masuk' => $karyawan->tgl_masuk,
                'unit_kerja' => $karyawan->unit_kerjas,
                'jabatan' => $karyawan->jabatans,
                'kompetensi' => $karyawan->kompetensis,
                'nik_ktp' => $karyawan->nik_ktp,
                'status_karyawan' => $karyawan->status_karyawans,
                'tempat_lahir' => $karyawan->tempat_lahir,
                'tgl_lahir' => $karyawan->tgl_lahir,
                'kelompok_gaji' => $karyawan->kelompok_gajis,
                'no_rekening' => $karyawan->no_rekening,
                'tunjangan_jabatan' => $karyawan->tunjangan_jabatan,
                'tunjangan_fungsional' => $karyawan->tunjangan_fungsional,
                'tunjangan_khusus' => $karyawan->tunjangan_khusus,
                'tunjangan_lainnya' => $karyawan->tunjangan_lainnya,
                'uang_lembur' => $karyawan->uang_lembur,
                'uang_makan' => $karyawan->uang_makan,
                'ptkp' => $karyawan->ptkps,
                'tgl_keluar' => $karyawan->tgl_keluar,
                'no_kk' => $karyawan->no_kk,
                'alamat' => $karyawan->alamat,
                'gelar_depan' => $karyawan->gelar_depan,
                'gelar_belakang' => $karyawan->gelar_belakang,
                'no_hp' => $karyawan->no_hp,
                'no_bpjsksh' => $karyawan->no_bpjsksh,
                'no_bpjsktk' => $karyawan->no_bpjsktk,
                'tgl_diangkat' => $karyawan->tgl_diangkat,
                'masa_kerja' => $karyawan->masa_kerja,
                'npwp' => $karyawan->npwp,
                'jenis_kelamin' => $karyawan->jenis_kelamin,
                'agama' => $karyawan->kategori_agamas,
                'golongan_darah' => $karyawan->kategori_darahs,
                'pendidikan_terakhir' => $karyawan->pendidikan_terakhir,
                'tinggi_badan' => $karyawan->tinggi_badan,
                'berat_badan' => $karyawan->berat_badan,
                'no_ijazah' => $karyawan->no_ijasah,
                'tahun_lulus' => $karyawan->tahun_lulus,
                'no_str' => $karyawan->no_str,
                'created_str' => $karyawan->created_str,
                'masa_berlaku_str' => $karyawan->masa_berlaku_str,
                'tgl_berakhir_pks' => $karyawan->tgl_berakhir_pks,
                'masa_diklat' => $karyawan->masa_diklat,
                'status_reward_presensi' => $karyawan->status_reward_presensi,
                'created_at' => $karyawan->created_at,
                'updated_at' => $karyawan->updated_at
            ];
        });
    }
}
