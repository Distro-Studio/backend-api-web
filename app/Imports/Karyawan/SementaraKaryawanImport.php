<?php

namespace App\Imports\Karyawan;

use App\Models\DataKaryawan;
use Illuminate\Support\Facades\Log;
use Maatwebsite\Excel\Concerns\ToModel;
use Maatwebsite\Excel\Concerns\Importable;
use Maatwebsite\Excel\Concerns\WithHeadingRow;
use Maatwebsite\Excel\Concerns\WithValidation;

class SementaraKaryawanImport implements ToModel, WithHeadingRow, WithValidation
{
    use Importable;

    // Relationship
    private $DataKaryawan;

    public function __construct()
    {
        $this->DataKaryawan = DataKaryawan::select('id', 'nik')->get();
    }

    public function rules(): array
    {
        return [
            'nik' => 'required|string',

            // tambahan
            'gelar_depan' => 'nullable',
            'gelar_belakang' => 'nullable',
            'tempat_lahir' => 'nullable',
            'alamat' => 'nullable',
            'no_hp' => 'nullable|numeric', // default 123
            'nik_ktp' => 'nullable|numeric', // default 123
            'no_kk' => 'nullable|numeric', // default 123
            'npwp' => 'nullable', // default 123
            'no_bpjsksh' => 'nullable|string', // default 123
            'no_bpjsktk' => 'nullable|string', // default 123
            'no_manulife' => 'nullable|string', // default 123
            'no_rm' => 'nullable|numeric', // default 123
        ];
    }

    public function customValidationMessages()
    {
        return [
            'nik.required' => 'NIK karyawan tidak diperbolehkan kosong.',
            'nik.string' => 'NIK karyawan tidak diperbolehkan mengandung angka.',
            'no_hp.numeric' => 'Nomor HP karyawan tidak diperbolehkan mengandung selain angka.',
            'nik_ktp.numeric' => 'NIK KTP karyawan tidak diperbolehkan mengandung selain angka.',
            'nik_ktp.max' => 'NIK KTP karyawan melebihi batas maksimum panjang 17 karakter.',
            'no_kk.numeric' => 'Nomor KK karyawan tidak diperbolehkan mengandung selain angka.',
            'npwp.numeric' => 'Nomor NPWP karyawan tidak diperbolehkan mengandung selain angka.',
            'no_bpjsksh.numeric' => 'Nomor BPJS KSH karyawan tidak diperbolehkan mengandung selain angka.',
            'no_bpjsktk.numeric' => 'Nomor BPJS KTK karyawan tidak diperbolehkan mengandung selain angka.',
            'no_manulife.numeric' => 'Nomor Manuife karyawan tidak diperbolehkan mengandung selain angka.',
            'no_rm.numeric' => 'Nomor RM karyawan tidak diperbolehkan mengandung selain angka.',
        ];
    }

    public function model(array $row)
    {
        // Pastikan NPWP diubah menjadi string tanpa format eksponensial
        $npwp = isset($row['npwp']) ? $this->convertScientificNotation($row['npwp']) : null;

        // Cari data karyawan berdasarkan NIK
        $dataKaryawan = $this->DataKaryawan->where('nik', $row['nik'])->first();

        if ($dataKaryawan) {
            // Update data karyawan
            $dataKaryawan->update([
                'gelar_depan' => $row['gelar_depan'] ?? $dataKaryawan->gelar_depan,
                'gelar_belakang' => $row['gelar_belakang'] ?? $dataKaryawan->gelar_belakang,
                'tempat_lahir' => $row['tempat_lahir'] ?? $dataKaryawan->tempat_lahir,
                'alamat' => $row['alamat'] ?? $dataKaryawan->alamat,
                'no_hp' => $row['no_hp'] ?? $dataKaryawan->no_hp,
                'nik_ktp' => $row['nik_ktp'] ?? $dataKaryawan->nik_ktp,
                'no_kk' => $row['no_kk'] ?? $dataKaryawan->no_kk,
                'npwp' => $npwp ?? $dataKaryawan->npwp,
                'no_bpjsksh' => $row['no_bpjsksh'] ?? $dataKaryawan->no_bpjsksh,
                'no_bpjsktk' => $row['no_bpjsktk'] ?? $dataKaryawan->no_bpjsktk,
                'no_manulife' => $row['no_manulife'] ?? $dataKaryawan->no_manulife,
                'no_rm' => $row['no_rm'] ?? $dataKaryawan->no_rm,
            ]);
        } else {
            Log::warning('Data karyawan tidak ditemukan untuk NIK: ' . $row['nik']);
        }
    }

    private function convertScientificNotation($value)
    {
        if (is_numeric($value) && strpos(strtolower($value), 'e') !== false) {
            // Ubah dari eksponensial ke string tanpa kehilangan presisi
            return number_format($value, 0, '', '');
        }

        return $value; // Jika bukan angka dalam format eksponensial, kembalikan nilai asli
    }
}
