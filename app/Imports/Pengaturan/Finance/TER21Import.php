<?php

namespace App\Imports\Pengaturan\Finance;

use App\Models\Ter;
use App\Models\Ptkp;
use App\Models\KategoriTer;
use Maatwebsite\Excel\Concerns\ToModel;
use Maatwebsite\Excel\Concerns\Importable;
use Maatwebsite\Excel\Concerns\WithHeadingRow;
use Maatwebsite\Excel\Concerns\WithValidation;

class TER21Import implements ToModel, WithHeadingRow, WithValidation
{
    use Importable;

    // Relationship
    private $kategoriTER;
    private $PTKP;

    public function __construct()
    {
        $this->kategoriTER = KategoriTer::select('id', 'nama_kategori_ter')->get();
        $this->PTKP = Ptkp::select('id', 'kode_ptkp')->get();
    }

    public function rules(): array
    {
        return [
            'kategori_ter_id' => 'required',
            'ptkp_id' => 'required',
            'from_ter' => 'required|numeric',
            'to_ter' => 'required|numeric',
            'percentage_ter' => 'required|numeric',
        ];
    }

    public function model(array $row)
    {
        // $kategoriTER = KategoriTer::where('nama_kategori_ter', $row['kategori_ter_id'])->pluck('id')->first();
        $kategoriTER = $this->kategoriTER->where('nama_kategori_ter', $row['nama_kategori_ter'])->first();
        $PTKP = $this->PTKP->where('kode_ptkp', $row['kode_ptkp'])->first();

        return new Ter([
            'kategori_ter_id' => $kategoriTER->id ?? NULL,
            'ptkp_id' => $PTKP->id ?? NULL,
            'from_ter' => $row['from_ter'],
            'to_ter' => $row['to_ter'],
            'percentage_ter' => $row['percentage_ter'],
        ]);
    }
}
