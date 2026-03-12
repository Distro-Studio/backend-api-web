<?php

namespace App\Exports\Perusahaan;

use App\Models\Diklat;
use Maatwebsite\Excel\Concerns\Exportable;
use Maatwebsite\Excel\Concerns\WithMultipleSheets;

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
            'kategori_diklats',
            'peserta_diklat.users.data_karyawans.unit_kerjas',
            'peserta_diklat.users.data_karyawans.jabatans',
            'peserta_diklat.users.data_karyawans.status_karyawans',
            'peserta_diklat.users.data_karyawans',
            'peserta_diklat.users',
        ])->where('kategori_diklat_id', 1)->orderBy('created_at', 'desc');

        // Filter by date range dinamis (payload d-m-Y, db bisa d-m-Y atau Y-m-d H:i:s)
        if (!empty($this->tglMulai) && !empty($this->tglSelesai)) {
            $start = \DateTime::createFromFormat('d-m-Y', $this->tglMulai)?->format('Y-m-d 00:00:00');
            $end = \DateTime::createFromFormat('d-m-Y', $this->tglSelesai)?->format('Y-m-d 23:59:59');
            if ($start && $end) {
                $query->where(function($q) use ($start, $end) {
                    // tgl_mulai format Y-m-d H:i:s
                    $q->orWhere(function($sub) use ($start, $end) {
                        $sub->whereRaw("STR_TO_DATE(tgl_mulai, '%Y-%m-%d %H:%i:%s') BETWEEN ? AND ?", [$start, $end]);
                    });
                    // tgl_mulai format d-m-Y
                    $q->orWhere(function($sub) use ($start, $end) {
                        $sub->whereRaw("STR_TO_DATE(tgl_mulai, '%d-%m-%Y') BETWEEN ? AND ?", [substr($start,0,10), substr($end,0,10)]);
                    });
                });
            }
        }

        $diklats = $query->get();
        $sheets = [];

        $rowsByUnitKerja = [];

        foreach ($diklats as $diklat) {
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

            foreach ($peserta as $pesertaDiklat) {
                $user = $pesertaDiklat->users;
                $dataKaryawan = $user->data_karyawans ?? null;
                $unitKerja = $dataKaryawan->unit_kerjas->nama_unit ?? 'Tanpa Unit Kerja';

                $rowsByUnitKerja[$unitKerja][] = [
                    'nama' => $user->nama ?? '-',
                    'nip' => $dataKaryawan->nik ?? '-',
                    'unit_kerja' => $unitKerja,
                    'nama_diklat' => $diklat->nama ?? '-',
                    'kategori_diklat' => $diklat->kategori_diklats->label ?? '-',
                    'kuota' => $diklat->kuota ?? '-',
                    'tanggal_mulai' => $diklat->tgl_mulai,
                    'tanggal_selesai' => $diklat->tgl_selesai,
                    'jam_mulai' => $diklat->jam_mulai,
                    'jam_selesai' => $diklat->jam_selesai,
                    'durasi' => $diklat->durasi,
                    'lokasi' => $diklat->lokasi ?? '-',
                ];
            }
        }

        ksort($rowsByUnitKerja);

        $usedSheetTitles = [];
        foreach ($rowsByUnitKerja as $unitKerja => $rows) {
            $sheetTitle = $this->makeSheetTitle($unitKerja, $usedSheetTitles);
            $sheets[] = new DiklatInternalUnitKerjaSheetExport(collect($rows), $sheetTitle);
        }

        if (empty($sheets)) {
            $sheets[] = new DiklatInternalUnitKerjaSheetExport(collect(), 'Data Diklat');
        }

        return $sheets;
    }

    private function makeSheetTitle(string $unitKerja, array &$usedSheetTitles): string
    {
        $sanitizedTitle = preg_replace('/[\\\\\/*?:\[\]]/', ' ', $unitKerja);
        $sanitizedTitle = trim(preg_replace('/\s+/', ' ', $sanitizedTitle));
        $baseTitle = $sanitizedTitle !== '' ? $sanitizedTitle : 'Tanpa Unit Kerja';
        $baseTitle = mb_substr($baseTitle, 0, 31);
        $candidate = $baseTitle;
        $suffix = 1;

        while (in_array($candidate, $usedSheetTitles, true)) {
            $suffixLabel = ' ' . $suffix;
            $candidate = mb_substr($baseTitle, 0, 31 - mb_strlen($suffixLabel)) . $suffixLabel;
            $suffix++;
        }

        $usedSheetTitles[] = $candidate;

        return $candidate;
    }
}
