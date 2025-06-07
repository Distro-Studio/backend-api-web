<?php

namespace App\Exports\Jadwal\CutiNew;

use App\Models\Cuti;
use App\Models\HakCuti;
use App\Models\User;
use Carbon\Carbon;
use Maatwebsite\Excel\Concerns\Exportable;
use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithMapping;
use Maatwebsite\Excel\Concerns\WithTitle;

class CutiBesarSheet implements FromCollection, WithHeadings, WithMapping, WithTitle
{
    use Exportable;

    private $number;
    // private $filters;
    private $title;
    private $startDate;
    private $endDate;
    private $tipeCutiId;

    public function __construct($title, $startDate, $endDate, $tipeCutiId)
    {
        // $this->filters = $filters;
        $this->title = $title;
        $this->startDate = $startDate;
        $this->endDate = $endDate;
        $this->tipeCutiId = $tipeCutiId;
        $this->number = 0;
    }

    public function collection()
    {
        $query = Cuti::query()
            ->where('tipe_cuti_id', $this->tipeCutiId)
            ->join('users', 'cutis.user_id', '=', 'users.id')
            ->join('data_karyawans', 'users.id', '=', 'data_karyawans.user_id')
            ->when($this->startDate && $this->endDate, function ($q) {
                $q->whereRaw("
                    STR_TO_DATE(tgl_from, '%d-%m-%Y') <= ?
                    AND STR_TO_DATE(tgl_to, '%d-%m-%Y') >= ?
                ", [$this->endDate, $this->startDate]);
            })
            ->select('cutis.user_id')
            ->distinct();

        // if (isset($this->filters['unit_kerja'])) {
        //     $namaUnitKerja = $this->filters['unit_kerja'];
        //     $query->whereHas('users.data_karyawans.unit_kerjas', function ($query) use ($namaUnitKerja) {
        //         if (is_array($namaUnitKerja)) {
        //             $query->whereIn('id', $namaUnitKerja);
        //         } else {
        //             $query->where('id', '=', $namaUnitKerja);
        //         }
        //     });
        // }

        // if (isset($this->filters['jabatan'])) {
        //     $namaJabatan = $this->filters['jabatan'];
        //     $query->whereHas('users.data_karyawans.jabatans', function ($query) use ($namaJabatan) {
        //         if (is_array($namaJabatan)) {
        //             $query->whereIn('id', $namaJabatan);
        //         } else {
        //             $query->where('id', '=', $namaJabatan);
        //         }
        //     });
        // }

        // if (isset($this->filters['status_karyawan'])) {
        //     $statusKaryawan = $this->filters['status_karyawan'];
        //     $query->whereHas('users.data_karyawans.status_karyawans', function ($query) use ($statusKaryawan) {
        //         if (is_array($statusKaryawan)) {
        //             $query->whereIn('id', $statusKaryawan);
        //         } else {
        //             $query->where('id', '=', $statusKaryawan);
        //         }
        //     });
        // }

        // if (isset($this->filters['masa_kerja'])) {
        //     $masaKerja = $this->filters['masa_kerja'];
        //     $currentDate = Carbon::now('Asia/Jakarta');
        //     if (is_array($masaKerja)) {
        //         $query->whereHas('users.data_karyawans', function ($query) use ($masaKerja, $currentDate) {
        //             foreach ($masaKerja as $masa) {
        //                 $bulan = $masa * 12;
        //                 $query->orWhereRaw("TIMESTAMPDIFF(MONTH, STR_TO_DATE(tgl_masuk, '%d-%m-%Y'), COALESCE(STR_TO_DATE(tgl_keluar, '%d-%m-%Y'), ?)) <= ?", [$currentDate, $bulan]);
        //             }
        //         });
        //     } else {
        //         $bulan = $masaKerja * 12;
        //         $query->whereHas('users.data_karyawans', function ($query) use ($bulan, $currentDate) {
        //             $query->whereRaw("TIMESTAMPDIFF(MONTH, STR_TO_DATE(tgl_masuk, '%d-%m-%Y'), COALESCE(STR_TO_DATE(tgl_keluar, '%d-%m-%Y'), ?)) <= ?", [$currentDate, $bulan]);
        //         });
        //     }
        // }

        // if (isset($this->filters['status_aktif'])) {
        //     $statusAktif = $this->filters['status_aktif'];
        //     $query->whereHas('users', function ($query) use ($statusAktif) {
        //         if (is_array($statusAktif)) {
        //             $query->whereIn('status_aktif', $statusAktif);
        //         } else {
        //             $query->where('status_aktif', '=', $statusAktif);
        //         }
        //     });
        // }

        // if (isset($this->filters['tgl_masuk'])) {
        //     $tglMasuk = $this->filters['tgl_masuk'];
        //     if (is_array($tglMasuk)) {
        //         $query->whereHas('users.data_karyawans', function ($query) use ($tglMasuk) {
        //             $query->whereIn('tgl_masuk', $tglMasuk);
        //         });
        //     } else {
        //         $query->whereHas('users.data_karyawans', function ($query) use ($tglMasuk) {
        //             $query->where('tgl_masuk', $tglMasuk);
        //         });
        //     }
        // }

        // if (isset($this->filters['agama'])) {
        //     $namaAgama = $this->filters['agama'];
        //     $query->whereHas('users.data_karyawans.kategori_agamas', function ($query) use ($namaAgama) {
        //         if (is_array($namaAgama)) {
        //             $query->whereIn('id', $namaAgama);
        //         } else {
        //             $query->where('id', '=', $namaAgama);
        //         }
        //     });
        // }

        // if (isset($this->filters['jenis_kelamin'])) {
        //     $jenisKelamin = $this->filters['jenis_kelamin'];
        //     if (is_array($jenisKelamin)) {
        //         $query->whereHas('users.data_karyawans', function ($query) use ($jenisKelamin) {
        //             $query->where(function ($query) use ($jenisKelamin) {
        //                 foreach ($jenisKelamin as $jk) {
        //                     $query->orWhere('jenis_kelamin', $jk);
        //                 }
        //             });
        //         });
        //     } else {
        //         $query->whereHas('users.data_karyawans', function ($query) use ($jenisKelamin) {
        //             $query->where('jenis_kelamin', $jenisKelamin);
        //         });
        //     }
        // }

        // if (isset($this->filters['pendidikan_terakhir'])) {
        //     $namaPendidikan = $this->filters['pendidikan_terakhir'];
        //     $query->whereHas('users.data_karyawans.kategori_pendidikans', function ($query) use ($namaPendidikan) {
        //         if (is_array($namaPendidikan)) {
        //             $query->whereIn('id', $namaPendidikan);
        //         } else {
        //             $query->where('id', '=', $namaPendidikan);
        //         }
        //     });
        // }

        // if (isset($this->filters['jenis_karyawan'])) {
        //     $jenisKaryawan = $this->filters['jenis_karyawan'];
        //     $query->whereHas('users.data_karyawans.unit_kerjas', function ($query) use ($jenisKaryawan) {
        //         if (is_array($jenisKaryawan)) {
        //             $query->whereIn('jenis_karyawan', $jenisKaryawan);
        //         } else {
        //             $query->where('jenis_karyawan', $jenisKaryawan);
        //         }
        //     });
        // }

        // if (isset($this->filters['jenis_kompetensi'])) {
        //     $jenisKompetensi = $this->filters['jenis_kompetensi'];
        //     $query->whereHas('users.data_karyawans.kompetensis', function ($query) use ($jenisKompetensi) {
        //         if (is_array($jenisKompetensi)) {
        //             $query->whereIn('jenis_kompetensi', $jenisKompetensi);
        //         } else {
        //             $query->where('jenis_kompetensi', $jenisKompetensi);
        //         }
        //     });
        // }

        // if (isset($this->filters['tipe_cuti'])) {
        //     $namaTipeCuti = $this->filters['tipe_cuti'];
        //     $query->whereHas('tipe_cutis', function ($query) use ($namaTipeCuti) {
        //         if (is_array($namaTipeCuti)) {
        //             $query->whereIn('id', $namaTipeCuti);
        //         } else {
        //             $query->where('id', '=', $namaTipeCuti);
        //         }
        //     });
        // }

        // Ambil distinct user_id saja
        $userIds = $query->pluck('user_id')->unique()->toArray();

        // Query user dengan relasi, order by nik
        $users = User::with(['data_karyawans', 'data_karyawans.hak_cutis' => function ($q) {
            $q->where('tipe_cuti_id', $this->tipeCutiId);
        }])
            ->whereIn('users.id', $userIds)
            ->join('data_karyawans', 'users.id', '=', 'data_karyawans.user_id')
            ->orderBy('data_karyawans.nik', 'asc')
            ->get(['users.*']);

        return $users;
    }

    public function headings(): array
    {
        $maxKuota = HakCuti::where('tipe_cuti_id', $this->tipeCutiId)
            ->max('kuota');

        $headings = ['no', 'nama', 'nik', 'sisa_kuota'];

        for ($i = 1; $i <= $maxKuota; $i++) {
            $headings[] = (string)$i;
        }

        return $headings;
    }

    public function map($user): array
    {
        $this->number++;

        $dataKaryawan = $user->data_karyawans;
        $hakCuti = $dataKaryawan ? $dataKaryawan->hak_cutis->first() : null;
        $kuota = $hakCuti->kuota ?? 0;
        $usedKuota = $hakCuti->used_kuota ?? 0;
        $sisaKuota = max(0, $kuota - $usedKuota);
        $nik = $dataKaryawan->nik ?? 'N/A';

        // Ambil data cuti user ini dengan tipe cuti yang sama
        $cutiUserCollection = Cuti::where('user_id', $user->id)
            ->where('status_cuti_id', 4)
            ->where('tipe_cuti_id', $this->tipeCutiId)
            ->orderBy('tgl_from', 'asc')
            ->get();

        // Contoh ambil tanggal cuti dalam format d/m/Y, gabungkan dalam array
        $tanggalCutiDipakai = [];
        foreach ($cutiUserCollection as $cuti) {
            $startDate = Carbon::parse($cuti->tgl_from);
            $endDate = Carbon::parse($cuti->tgl_to);

            for ($date = $startDate->copy(); $date->lte($endDate); $date->addDay()) {
                $tanggalCutiDipakai[] = $date->format('d/m/Y');
            }
        }
        $tanggalCutiDipakai = array_values(array_unique($tanggalCutiDipakai));

        // Urutkan tanggal secara kronologis
        usort($tanggalCutiDipakai, function ($a, $b) {
            return Carbon::createFromFormat('d/m/Y', $a)->timestamp - Carbon::createFromFormat('d/m/Y', $b)->timestamp;
        });

        $maxKuota = max($kuota, count($tanggalCutiDipakai));

        $row = [
            $this->number,
            $user->nama,
            $nik,
            $sisaKuota,
        ];

        // Tambahkan kolom tanggal cuti sesuai max kuota
        for ($i = 0; $i < $maxKuota; $i++) {
            $row[] = $tanggalCutiDipakai[$i] ?? 'N/A';
        }

        return $row;
    }

    public function title(): string
    {
        return $this->title;
    }
}
