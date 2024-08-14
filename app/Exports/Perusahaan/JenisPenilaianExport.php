<?php

namespace App\Exports\Perusahaan;

use Carbon\Carbon;
use App\Models\Diklat;
use App\Helpers\RandomHelper;
use App\Models\JenisPenilaian;
use Maatwebsite\Excel\Concerns\Exportable;
use Maatwebsite\Excel\Concerns\WithMapping;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\FromCollection;

class JenisPenilaianExport implements FromCollection, WithHeadings, WithMapping
{
    use Exportable;

    private static $number = 0;

    public function collection()
    {
        return JenisPenilaian::with(['status_karyawans', 'unit_kerjas', 'role_penilais', 'role_dinilais', 'penilaians'])->get();
    }

    public function headings(): array
    {
        return [
            'no',
            'nama_jenis_penilaian',
            'tgl_mulai',
            'tgl_selesai',
            'status_karyawan',
            'role_penilai',
            'role_dinilai',
            'unit_kerja',
            'created_at',
            'updated_at',
        ];
    }

    public function map($jenis_penilaian): array
    {
        self::$number++;
        $convertTanggal_Mulai = RandomHelper::convertToDateString($jenis_penilaian->tgl_mulai);
        $convertTanggal_Selesai = RandomHelper::convertToDateString($jenis_penilaian->tgl_selesai);
        $tgl_mulai = Carbon::parse($convertTanggal_Mulai)->format('d-m-Y');
        $tgl_selesai = Carbon::parse($convertTanggal_Selesai)->format('d-m-Y');

        return [
            self::$number,
            $jenis_penilaian->nama,
            $tgl_mulai,
            $tgl_selesai,
            $jenis_penilaian->status_karyawans->label,
            $jenis_penilaian->role_penilais->name,
            $jenis_penilaian->role_dinilais->name,
            $jenis_penilaian->unit_kerjas->nama_unit,
            Carbon::parse($jenis_penilaian->created_at)->format('d-m-Y H:i:s'),
            Carbon::parse($jenis_penilaian->updated_at)->format('d-m-Y H:i:s'),
        ];
    }
}
