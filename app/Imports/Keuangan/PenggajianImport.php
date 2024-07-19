<?php

namespace App\Imports\Keuangan;

use App\Models\User;
use App\Models\Penggajian;
use App\Models\DataKaryawan;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Maatwebsite\Excel\Concerns\ToModel;
use Maatwebsite\Excel\Validators\Failure;
use Maatwebsite\Excel\Concerns\Importable;
use Maatwebsite\Excel\Concerns\WithHeadingRow;
use Maatwebsite\Excel\Concerns\WithValidation;
use App\Http\Resources\Publik\WithoutData\WithoutDataResource;
use Maatwebsite\Excel\Concerns\SkipsErrors;
use Maatwebsite\Excel\Concerns\SkipsFailures;

class PenggajianImport implements ToModel, WithHeadingRow, WithValidation
{
    use Importable, SkipsErrors, SkipsFailures;

    private $data_karaywans;

    public function __construct()
    {
        $this->data_karaywans = DataKaryawan::with('kelompok_gajis')->get();
    }

    public function rules(): array
    {
        return [
            'tgl_penggajian' => 'required',
            'nama' => 'required|string|exists:users,nama',
            'gaji_pokok' => 'required|numeric',
            'total_tunjangan' => 'required|numeric',
            'reward' => 'required|numeric',
            'gaji_bruto' => 'required|numeric',
            'total_premi' => 'required|numeric',
            'pph_21' => 'required|numeric',
            'take_home_pay' => 'required|numeric',
            'status_penggajian' => 'required|string', // if row disetujui = 1, else = 0
        ];
    }

    public function customValidationMessages()
    {
        return [
            'tgl_penggajian.required' => 'Tanggal tidak diperbolehkan kosong.',
            'nama.required' => 'Nama karyawan tidak diperbolehkan kosong.',
            'nama.string' => 'Nama karyawan tidak diperbolehkan mengandung angka.',
            'nama.exists' => 'Nama karyawan tersebut tidak ditemukan pada database.',
            'gaji_pokok.required' => 'Gaji Pokok karyawan tidak diperbolehkan kosong.',
            'gaji_pokok.numeric' => 'Gaji Pokok karyawan tidak diperbolehkan mengandung huruf.',
            'total_tunjangan.required' => 'Total Tunjangan karyawan tidak diperbolehkan kosong.',
            'total_tunjangan.numeric' => 'Total Tunjangan karyawan tidak diperbolehkan mengandung huruf.',
            'reward.required' => 'Reward karyawan tidak diperbolehkan kosong.',
            'reward.numeric' => 'Reward karyawan tidak diperbolehkan mengandung huruf.',
            'gaji_bruto.required' => 'Gaji bruto karyawan tidak diperbolehkan kosong.',
            'gaji_bruto.numeric' => 'Gaji bruto karyawan tidak diperbolehkan mengandung huruf.',
            'total_premi.required' => 'Total premi karyawan tidak diperbolehkan kosong.',
            'total_premi.numeric' => 'Total premi karyawan tidak diperbolehkan mengandung huruf.',
            'pph_21.required' => 'PPH 21 karyawan tidak diperbolehkan kosong.',
            'pph_21.numeric' => 'PPH 21 karyawan tidak diperbolehkan mengandung huruf.',
            'take_home_pay.required' => 'Gaji bersih karyawan tidak diperbolehkan kosong.',
            'take_home_pay.numeric' => 'Gaji bersih karyawan tidak diperbolehkan mengandung huruf.',
        ];
    }

    public function model(array $row)
    {
        // Temukan data karyawan berdasarkan nama pengguna
        $user = User::where('nama', $row['nama'])->first();

        if ($user) {
            // Temukan data_karyawan_id yang sesuai berdasarkan user_id dari tabel data_karyawans
            $data_karyawan = $this->data_karaywans->where('user_id', $user->id)->first();

            if ($data_karyawan) {
                $data_karyawan_id = $data_karyawan->id;
            }
        }

        // Setel status_penggajian ke 0 jika baris adalah 'disetujui', selain itu 1
        $status_penggajian = strtolower($row['status_penggajian']) == 'disetujui' ? 0 : 1;
        return new Penggajian([
            'tgl_penggajian' => $row['tgl_penggajian'],
            'data_karyawan_id' => $data_karyawan_id, // row nama
            'gaji_pokok' => $row['gaji_pokok'],
            'total_tunjangan' => $row['total_tunjangan'],
            'reward' => $row['reward'],
            'gaji_bruto' => $row['gaji_bruto'],
            'total_premi' => $row['total_premi'],
            'pph_21' => $row['pph_21'],
            'take_home_pay' => $row['take_home_pay'],
            'status_penggajian' => $status_penggajian,
        ]);
    }

    public function onFailure(Failure ...$failures)
    {
        foreach ($failures as $failure) {
            Log::error('Row ' . $failure->row() . ' - ' . implode(', ', $failure->errors()));
        }
    }
}
