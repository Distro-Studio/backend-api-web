<?php

namespace App\Exports\Perusahaan;

use Carbon\Carbon;
use App\Models\Penilaian;
use App\Helpers\RandomHelper;
use Maatwebsite\Excel\Concerns\Exportable;
use Maatwebsite\Excel\Concerns\WithMapping;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\FromCollection;

class PenilaianExport implements FromCollection, WithHeadings, WithMapping
{
    use Exportable;

    private static $number = 0;

    public function collection()
    {
        return Penilaian::with(['user_dinilais', 'user_penilais', 'unit_kerja_dinilais', 'unit_kerja_penilais', 'jabatan_dinilais', 'jabatan_penilais'])->get();
    }

    public function headings(): array
    {
        return [
            'no',
            'periode',
            'karyawan_dinilai',
            'unit_kerja_karyawan_dinilai',
            'jabatan_karyawan_dinilai',
            'rata_rata_karyawan_dinilai',
            'karyawan_penilai',
            'unit_kerja_karyawan_penilai',
            'jabatan_karyawan_penilai',
            'created_at'
        ];
    }

    public function map($penilaian): array
    {
        self::$number++;

        return [
            self::$number,
            Carbon::parse($penilaian->created_at)->locale('id')->isoFormat('MMMM Y'),
            $penilaian->user_dinilais->nama,
            $penilaian->unit_kerja_dinilais->nama_unit,
            $penilaian->jabatan_dinilais->nama_jabatan,
            $penilaian->rata_rata,
            $penilaian->user_penilais->nama,
            $penilaian->unit_kerja_penilais->nama_unit,
            $penilaian->jabatan_penilais->nama_jabatan,
            Carbon::parse($penilaian->created_at)->format('d-m-Y H:i:s')
        ];
    }
}
