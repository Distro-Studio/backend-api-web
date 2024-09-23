<?php

namespace App\Exports\Keuangan;

use Carbon\Carbon;
use App\Models\TagihanPotongan;
use Maatwebsite\Excel\Concerns\Exportable;
use Maatwebsite\Excel\Concerns\WithMapping;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\FromCollection;

class TagihanPotonganExport implements FromCollection, WithHeadings, WithMapping
{
    use Exportable;

    private static $number = 0;

    public function collection()
    {
        return TagihanPotongan::with([
            'tagihan_karyawans.users',
            'tagihan_kategoris',
            'tagihan_status'
        ])->get();
    }

    public function headings(): array
    {
        return [
            'no',
            'nama',
            'kategori_tagihan',
            'status_tagihan',
            'nominal',
            'sisa_tagihan',
            'bulan_mulai_tertagih',
            'bulan_selesai_tertagih',
            'total_angsuran',
            'sisa_angsuran',
            'created_at',
            'updated_at'
        ];
    }

    public function map($tagihan_potongan): array
    {
        self::$number++;
        $sisaTagihan = $tagihan_potongan->sisa_tagihan === 0 || is_null($tagihan_potongan->sisa_tagihan) ? 'N/A' : $tagihan_potongan->sisa_tagihan;

        return [
            self::$number,
            $tagihan_potongan->tagihan_karyawans->users->nama,
            $tagihan_potongan->tagihan_kategoris->label,
            $tagihan_potongan->tagihan_status->label,
            $tagihan_potongan->besaran,
            $sisaTagihan,
            $tagihan_potongan->bulan_mulai ?? 'N/A',
            $tagihan_potongan->bulan_selesai ?? 'N/A',
            $tagihan_potongan->tenor ? $tagihan_potongan->tenor . ' kali angsuran' : 'N/A',
            $tagihan_potongan->sisa_tenor ? $tagihan_potongan->sisa_tenor . ' kali angsuran' : 'N/A',
            Carbon::parse($tagihan_potongan->created_at)->format('d-m-Y H:i:s'),
            Carbon::parse($tagihan_potongan->updated_at)->format('d-m-Y H:i:s')
        ];
    }
}
