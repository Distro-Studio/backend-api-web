<?php

namespace App\Imports\Keuangan;

use Carbon\Carbon;
use App\Models\User;
use App\Models\TagihanPotongan;
use App\Models\StatusTagihanPotongan;
use App\Models\KategoriTagihanPotongan;
use Maatwebsite\Excel\Concerns\ToModel;
use Maatwebsite\Excel\Concerns\Importable;
use Maatwebsite\Excel\Concerns\WithHeadingRow;
use Maatwebsite\Excel\Concerns\WithValidation;

class TagihanPotonganImport implements ToModel, WithHeadingRow, WithValidation
{
    use Importable;

    private $User;
    private $KategoriTagihan;

    public function __construct()
    {
        $this->User = User::select('id', 'nama')->get();
        $this->KategoriTagihan = KategoriTagihanPotongan::select('id', 'label')->get();
    }

    public function rules(): array
    {
        return [
            'nama' => 'required|string',
            'kategori_tagihan' => 'required|string',
            'besaran' => 'required|numeric',
            'bulan_mulai' => 'nullable|string',
            'bulan_selesai' => 'nullable|string',
        ];
    }

    public function customValidationMessages()
    {
        return [
            'nama.required' => 'Nama karyawan tidak diperbolehkan kosong.',
            'nama.string' => 'Nama karyawan hanya diperbolehkan mengandung huruf.',
            'kategori_tagihan.required' => 'Kategori tagihan potongan tidak diperbolehkan kosong.',
            'kategori_tagihan.string' => 'Kategori tagihan potongan tidak diperbolehkan mengandung angka.',
            'besaran.required' => 'Besaran pengurang gaji tidak diperbolehkan kosong.',
            'besaran.numeric' => 'Besaran pengurang gaji tidak diperbolehkan mengandung huruf.',
            'bulan_mulai.string' => 'Data tanggal mulai hanya diperbolehkan mengandung huruf dan angka.',
            'bulan_selesai.string' => 'Data tanggal selesai hanya diperbolehkan mengandung huruf dan angka.',
        ];
    }

    public function model(array $row)
    {
        $user = $this->User->where('nama', $row['nama'])->first();
        if (!$user) {
            throw new \Exception("Karyawan '" . $row['nama'] . "' tidak ditemukan.");
        }
        $dataKaryawan = $user->data_karyawans()->first();
        if (!$dataKaryawan) {
            throw new \Exception("Karyawan '" . $row['nama'] . "' tidak memiliki data karyawan.");
        }

        $kategoriTagihan = $this->KategoriTagihan->where('label', $row['kategori_tagihan'])->first();
        if (!$kategoriTagihan) {
            throw new \Exception("Kategori tagihan '" . $row['kategori_tagihan'] . "' tidak ditemukan.");
        }

        $bulanMulai = !empty($row['bulan_mulai']) ? Carbon::createFromFormat('d-m-Y', $row['bulan_mulai']) : null;
        $bulanSelesai = !empty($row['bulan_selesai']) ? Carbon::createFromFormat('d-m-Y', $row['bulan_selesai']) : null;
        $tenor = 0;
        if ($bulanMulai && $bulanSelesai) {
            $tenor = $bulanMulai->diffInMonths($bulanSelesai);
        }

        return new TagihanPotongan([
            'data_karyawan_id' => $dataKaryawan->id,
            'kategori_tagihan_id' => $kategoriTagihan->id,
            'status_tagihan_id' => 1,
            'besaran' => $row['besaran'],
            'tenor' => $tenor,
            'bulan_mulai' => $row['bulan_mulai'] ?? null,
            'bulan_selesai' => $row['bulan_selesai'] ?? null,
        ]);
    }
}
