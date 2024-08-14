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
        return Penilaian::with([
            'user_dinilais.data_karyawans.jabatans',
            'user_penilais.data_karyawans.jabatans',
            'jenis_penilaians'
        ])->get();
    }

    public function headings(): array
    {
        return [
            'no',
            'jenis_penilaian',
            'karyawan_dinilai',
            'unit_kerja_karyawan_dinilai',
            'jabatan_karyawan_dinilai',
            'karyawan_penilai',
            'unit_kerja_karyawan_penilai',
            'jabatan_karyawan_penilai',
            'rata_rata',
            'total_pertanyaan',
            'created_at'
        ];
    }

    public function map($penilaian): array
    {
        self::$number++;

        return [
            self::$number,
            optional($penilaian->jenis_penilaians)->nama,
            $penilaian->user_dinilais->nama,
            optional($penilaian->user_dinilais->data_karyawans->unit_kerjas)->nama_unit,
            optional($penilaian->user_dinilais->data_karyawans->jabatans)->nama_jabatan,
            $penilaian->user_penilais->nama,
            optional($penilaian->user_penilais->data_karyawans->unit_kerjas)->nama_unit,
            optional($penilaian->user_penilais->data_karyawans->jabatans)->nama_jabatan,
            $penilaian->rata_rata,
            $penilaian->total_pertanyaan,
            Carbon::parse($penilaian->created_at)->format('d-m-Y H:i:s')
        ];
    }
}
