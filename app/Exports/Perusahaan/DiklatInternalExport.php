<?php

namespace App\Exports\Perusahaan;

use App\Models\Diklat;
use Maatwebsite\Excel\Concerns\Exportable;
use Maatwebsite\Excel\Concerns\WithMultipleSheets;
use App\Exports\Perusahaan\PesertaDiklatSheetExport;

class DiklatInternalExport implements WithMultipleSheets
{
    use Exportable;

    private $tglMulai;
    private $tglSelesai;
    private $filters;

    public function __construct($tglMulai = null, $tglSelesai = null, $filters = [])
    {
        $this->tglMulai = $tglMulai;
        $this->tglSelesai = $tglSelesai;
        $this->filters = $filters;
    }


    public function sheets(): array
    {
        $query = Diklat::with([
            'peserta_diklat.users.data_karyawans.unit_kerjas',
            'peserta_diklat.users.data_karyawans.jabatans',
            'peserta_diklat.users.data_karyawans.status_karyawans',
            'peserta_diklat.users.data_karyawans',
            'peserta_diklat.users',
        ])->where('kategori_diklat_id', 1)->orderBy('created_at', 'desc');

        // Filter by date range
        if (!empty($this->tglMulai) && !empty($this->tglSelesai)) {
            $query->whereBetween('tgl_mulai', [$this->tglMulai, $this->tglSelesai]);
        }

        $diklats = $query->get();
        $sheets = [];
        foreach ($diklats as $diklat) {
            // Filter peserta diklat sesuai filter
            $peserta = collect($diklat->peserta_diklat)->filter(function ($peserta) {
                $user = $peserta->users;
                $dataKaryawan = $user->data_karyawans ?? null;
                $pass = true;
                if (isset($this->filters['user_id'])) {
                    $userId = $this->filters['user_id'];
                    $pass = $pass && (is_array($userId) ? in_array($user->id, $userId) : $user->id == $userId);
                }
                if (isset($this->filters['unit_kerja']) && $dataKaryawan) {
                    $unitKerja = $this->filters['unit_kerja'];
                    $unitId = $dataKaryawan->unit_kerjas->id ?? null;
                    $pass = $pass && (is_array($unitKerja) ? in_array($unitId, $unitKerja) : $unitId == $unitKerja);
                }
                if (isset($this->filters['jabatan']) && $dataKaryawan) {
                    $jabatan = $this->filters['jabatan'];
                    $jabatanId = $dataKaryawan->jabatans->id ?? null;
                    $pass = $pass && (is_array($jabatan) ? in_array($jabatanId, $jabatan) : $jabatanId == $jabatan);
                }
                if (isset($this->filters['status_karyawan']) && $dataKaryawan) {
                    $statusKaryawan = $this->filters['status_karyawan'];
                    $statusId = $dataKaryawan->status_karyawans->id ?? null;
                    $pass = $pass && (is_array($statusKaryawan) ? in_array($statusId, $statusKaryawan) : $statusId == $statusKaryawan);
                }
                if (isset($this->filters['status_aktif'])) {
                    $statusAktif = $this->filters['status_aktif'];
                    $pass = $pass && (is_array($statusAktif) ? in_array($user->status_aktif, $statusAktif) : $user->status_aktif == $statusAktif);
                }
                if (isset($this->filters['jenis_kelamin']) && $dataKaryawan) {
                    $jenisKelamin = $this->filters['jenis_kelamin'];
                    $jk = $dataKaryawan->jenis_kelamin ?? null;
                    $pass = $pass && (is_array($jenisKelamin) ? in_array($jk, $jenisKelamin) : $jk == $jenisKelamin);
                }
                return $pass;
            });
            $sheets[] = new PesertaDiklatSheetExport($diklat, $peserta);
        }
        return $sheets;
    }

    // Tidak perlu headings/map/formatDuration lagi
}
