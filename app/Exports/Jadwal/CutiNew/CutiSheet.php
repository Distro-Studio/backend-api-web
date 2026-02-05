<?php

namespace App\Exports\Jadwal\CutiNew;

use Carbon\Carbon;
use App\Models\Cuti;
use App\Helpers\RandomHelper;
use App\Models\HakCuti;
use App\Models\User;
use Maatwebsite\Excel\Concerns\WithTitle;
use Maatwebsite\Excel\Concerns\Exportable;
use Maatwebsite\Excel\Concerns\WithMapping;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\FromCollection;

class CutiSheet implements FromCollection, WithHeadings, WithMapping, WithTitle
{
    use Exportable;

    private $number;
    private $filters;
    private $title;
    private $startDate;
    private $endDate;
    private $tipeCutiId;

    public function __construct($filters = [], $title, $startDate, $endDate, $tipeCutiId)
    {
        $this->filters = $filters;
        $this->title = $title;
        $this->startDate = $startDate;
        $this->endDate = $endDate;
        $this->tipeCutiId = $tipeCutiId;
        $this->number = 0;
    }

    public function collection()
    {
        $query = Cuti::with(['users', 'tipe_cutis', 'status_cutis'])
            ->where('tipe_cuti_id', $this->tipeCutiId)
            ->join('users', 'cutis.user_id', '=', 'users.id')
            ->join('data_karyawans', 'users.id', '=', 'data_karyawans.user_id')
            ->when($this->startDate && $this->endDate, function ($q) {
                $q->whereRaw("
                    STR_TO_DATE(tgl_from, '%d-%m-%Y') <= ?
                    AND STR_TO_DATE(tgl_to, '%d-%m-%Y') >= ?
                ", [$this->endDate, $this->startDate]);
            })
            ->orderBy('data_karyawans.nik', 'asc')
            ->select('cutis.*');

        if (isset($this->filters['unit_kerja'])) {
            $namaUnitKerja = $this->filters['unit_kerja'];
            $query->whereHas('users.data_karyawans.unit_kerjas', function ($query) use ($namaUnitKerja) {
                if (is_array($namaUnitKerja)) {
                    $query->whereIn('id', $namaUnitKerja);
                } else {
                    $query->where('id', '=', $namaUnitKerja);
                }
            });
        }

        return $query->get();
    }

    public function headings(): array
    {
        return [
            'no',
            'nama',
            'nik',
            'tipe_cuti',
            'keterangan',
            'tgl_from',
            'tgl_to',
            'catatan',
            'durasi',
            'sisa_kuota',
            'status_cuti',
            'created_at',
            'updated_at',
        ];
    }

    public function map($cuti): array
    {
        $this->number++;

        $convertTgl_From = RandomHelper::convertToDateString($cuti->tgl_from);
        $convertTgl_To = RandomHelper::convertToDateString($cuti->tgl_to);
        $tgl_from = Carbon::parse($convertTgl_From)->format('d-m-Y');
        $tgl_to = Carbon::parse($convertTgl_To)->format('d-m-Y');

        // Cek apakah tipe cutinya unlimited
        $isUnlimited = (bool) optional($cuti->tipe_cutis)->is_unlimited;

        if ($isUnlimited) {
            // Untuk tipe cuti unlimited, sisa_kuota ditampilkan 'N/A'
            $sisaKuotaDisplay = 'N/A';
        } else {
            // Untuk tipe cuti biasa, pakai kuota dari hak_cuti (kalau ada), default 0 Hari
            $kuota = ($cuti->hak_cutis && $cuti->hak_cutis->kuota !== null)
                ? $cuti->hak_cutis->kuota
                : ($cuti->tipe_cutis->kuota ?? 0);

            // Hitung jumlah hari cuti yang sudah digunakan oleh user untuk tipe ini pada tahun berjalan
            $userId = $cuti->users->id ?? null;
            $usedDays = 0;
            if ($userId) {
                $usedDays = Cuti::where('tipe_cuti_id', $cuti->tipe_cuti_id)
                    ->where('status_cuti_id', 4)
                    ->where('user_id', $userId)
                    ->whereYear('created_at', Carbon::now('Asia/Jakarta')->year)
                    ->get()
                    ->sum(function ($c) {
                        if (!empty($c->durasi)) {
                            return $c->durasi;
                        }
                        $from = Carbon::parse($c->tgl_from);
                        $to = Carbon::parse($c->tgl_to);
                        return $from->diffInDays($to) + 1;
                    });
            }

            $sisa = max(0, $kuota - $usedDays);
            $sisaKuotaDisplay = $sisa . ' Hari';
        }

        return [
            $this->number,
            $cuti->users->nama,
            $cuti->users->data_karyawans->nik,
            $cuti->tipe_cutis->nama,
            $cuti->keterangan ?? 'N/A',
            $tgl_from,
            $tgl_to,
            $cuti->catatan ?? 'N/A',
            $cuti->durasi . ' Hari',
            $sisaKuotaDisplay,
            $cuti->status_cutis->label,
            Carbon::parse($cuti->created_at)->format('d-m-Y H:i:s'),
            Carbon::parse($cuti->updated_at)->format('d-m-Y H:i:s')
        ];
    }

    public function title(): string
    {
        return $this->title;
    }
}
