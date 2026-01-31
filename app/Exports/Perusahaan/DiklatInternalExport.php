<?php

namespace App\Exports\Perusahaan;

use Carbon\Carbon;
use App\Models\Diklat;
use App\Helpers\RandomHelper;
use Maatwebsite\Excel\Concerns\Exportable;
use Maatwebsite\Excel\Concerns\WithMapping;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\FromCollection;

class DiklatInternalExport implements FromCollection, WithHeadings, WithMapping
{
    use Exportable;

    private static $number = 0;
    private $tglMulai;
    private $tglSelesai;
    private $filters;

    public function __construct($tglMulai = null, $tglSelesai = null, $filters = [])
    {
        $this->tglMulai = $tglMulai;
        $this->tglSelesai = $tglSelesai;
        $this->filters = $filters;
    }

    public function collection()
    {
        $query = Diklat::with(['kategori_diklats', 'status_diklats', 'peserta_diklat.users.data_karyawans.unit_kerjas', 'peserta_diklat.users.data_karyawans.jabatans', 'peserta_diklat.users.data_karyawans.status_karyawans'])
            ->where('kategori_diklat_id', 1)
            ->orderBy('created_at', 'desc');

        // Filter by date range
        if (!empty($this->tglMulai) && !empty($this->tglSelesai)) {
            $query->whereBetween('tgl_mulai', [$this->tglMulai, $this->tglSelesai]);
        }

        // Filter by karyawan (user_id)
        if (isset($this->filters['user_id'])) {
            $userId = $this->filters['user_id'];
            $query->whereHas('peserta_diklat', function ($q) use ($userId) {
                if (is_array($userId)) {
                    $q->whereIn('user_id', $userId);
                } else {
                    $q->where('user_id', '=', $userId);
                }
            });
        }

        // Filter by unit_kerja
        if (isset($this->filters['unit_kerja'])) {
            $unitKerja = $this->filters['unit_kerja'];
            $query->whereHas('peserta_diklat.users.data_karyawans.unit_kerjas', function ($q) use ($unitKerja) {
                if (is_array($unitKerja)) {
                    $q->whereIn('id', $unitKerja);
                } else {
                    $q->where('id', '=', $unitKerja);
                }
            });
        }

        // Filter by jabatan
        if (isset($this->filters['jabatan'])) {
            $jabatan = $this->filters['jabatan'];
            $query->whereHas('peserta_diklat.users.data_karyawans.jabatans', function ($q) use ($jabatan) {
                if (is_array($jabatan)) {
                    $q->whereIn('id', $jabatan);
                } else {
                    $q->where('id', '=', $jabatan);
                }
            });
        }

        // Filter by status_karyawan
        if (isset($this->filters['status_karyawan'])) {
            $statusKaryawan = $this->filters['status_karyawan'];
            $query->whereHas('peserta_diklat.users.data_karyawans.status_karyawans', function ($q) use ($statusKaryawan) {
                if (is_array($statusKaryawan)) {
                    $q->whereIn('id', $statusKaryawan);
                } else {
                    $q->where('id', '=', $statusKaryawan);
                }
            });
        }

        // Filter by status_aktif (active status of user)
        if (isset($this->filters['status_aktif'])) {
            $statusAktif = $this->filters['status_aktif'];
            $query->whereHas('peserta_diklat.users', function ($q) use ($statusAktif) {
                if (is_array($statusAktif)) {
                    $q->whereIn('status_aktif', $statusAktif);
                } else {
                    $q->where('status_aktif', '=', $statusAktif);
                }
            });
        }

        // Filter by jenis_kelamin
        if (isset($this->filters['jenis_kelamin'])) {
            $jenisKelamin = $this->filters['jenis_kelamin'];
            if (is_array($jenisKelamin)) {
                $query->whereHas('peserta_diklat.users.data_karyawans', function ($q) use ($jenisKelamin) {
                    $q->where(function ($query) use ($jenisKelamin) {
                        foreach ($jenisKelamin as $jk) {
                            $query->orWhere('jenis_kelamin', $jk);
                        }
                    });
                });
            } else {
                $query->whereHas('peserta_diklat.users.data_karyawans', function ($q) use ($jenisKelamin) {
                    $q->where('jenis_kelamin', $jenisKelamin);
                });
            }
        }

        return $query->get();
    }

    public function headings(): array
    {
        return [
            'no',
            'nama_diklat',
            'kategori_diklat',
            'status_diklat',
            'deskripsi',
            'kuota',
            'tgl_mulai',
            'tgl_selesai',
            'jam_mulai',
            'jam_selesai',
            'durasi',
            'lokasi',
            'peserta_diklat',
            'created_at',
            'updated_at',
        ];
    }

    public function map($diklat): array
    {
        self::$number++;

        $pesertaDiklat = $diklat->peserta_diklat->map(function ($peserta) {
            return $peserta->users->nama ?? 'N/A';
        })->join(', ');

        return [
            self::$number,
            $diklat->nama,
            $diklat->kategori_diklats->label,
            $diklat->status_diklats->label,
            $diklat->deskripsi,
            $diklat->kuota . ' Peserta',
            $diklat->tgl_mulai,
            $diklat->tgl_selesai,
            $diklat->jam_mulai,
            $diklat->jam_selesai,
            $this->formatDuration($diklat->durasi),
            $diklat->lokasi,
            $pesertaDiklat,
            Carbon::parse($diklat->created_at)->format('d-m-Y H:i:s'),
            Carbon::parse($diklat->updated_at)->format('d-m-Y H:i:s')
        ];
    }

    private function formatDuration($seconds)
    {
        $hours = floor($seconds / 3600);
        $minutes = floor(($seconds % 3600) / 60);
        return sprintf("%d jam %d menit", $hours, $minutes);
    }
}
