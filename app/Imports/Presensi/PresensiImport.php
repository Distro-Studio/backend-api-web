<?php

namespace App\Imports\Presensi;

use App\Models\User;
use App\Models\Shift;
use App\Models\Presensi;
use App\Models\UnitKerja;
use Illuminate\Support\Str;
use App\Models\DataKaryawan;
use Maatwebsite\Excel\Concerns\ToModel;
use Maatwebsite\Excel\Concerns\Importable;
use Maatwebsite\Excel\Concerns\WithHeadingRow;
use Maatwebsite\Excel\Concerns\WithValidation;

class PresensiImport implements ToModel, WithHeadingRow, WithValidation
{
    use Importable;

    private $User;
    private $Shift;
    private $UnitKerja;
    public function __construct()
    {
        $this->User = User::select('id', 'nama')->get();
        $this->Shift = Shift::select('id', 'nama')->get();
        $this->UnitKerja = UnitKerja::select('id', 'nama_unit')->get();
    }

    public function rules(): array
    {
        return [
            'nama' => 'required|string|max:225|exists:users,nama',
            'shift' => 'required|string|exists:shifts,nama',
            'unit_kerja' => 'required|string|exists:unit_kerjas,nama_unit',
            'jam_masuk' => 'required|date',
            'jam_keluar' => 'required|date',
            'durasi' => 'required|numeric',
            'bukti_absensi' => 'required|image|mimes:jpg,jpeg,png|max:2048',
            'latitude' => 'required',
            'longtitude' => 'required',
            'absensi' => 'required|string',
            'kategori' => 'required',
        ];
    }

    public function customValidationMessages()
    {
        return [
            'nama.required' => 'Nama karyawan tidak diperbolehkan kosong.',
            'nama.string' => 'Nama karyawan tidak diperbolehkan mengandung angka.',
            'nama.max' => 'Nama karyawan melebihi batas maksimum panjang karakter.',
            'nama.exists' => 'Maaf nama tersebut tidak tersedia.',
            'shift.required' => 'Shift karyawan tidak diperbolehkan kosong.',
            'shift.string' => 'Shift karyawan tidak diperbolehkan mengandung angka.',
            'shift.exists' => 'Maaf shift tersebut tidak tersedia.',
            'unit_kerja.required' => 'Unit kerja karyawan tidak diperbolehkan kosong.',
            'unit_kerja.string' => 'Unit kerja karyawan tidak diperbolehkan mengandung angka.',
            'unit_kerja.exists' => 'Maaf unit kerja tersebut tidak tersedia.',
            'jam_masuk.required' => 'Tanggal presensi masuk tidak diperbolehkan kosong.',
            'jam_masuk.date' => 'Format tanggal presensi masuk tidak sesuai.',
            'jam_keluar.required' => 'Tanggal presensi keluar tidak diperbolehkan kosong.',
            'jam_keluar.date' => 'Format tanggal presensi keluar tidak sesuai.',
            'durasi.required' => 'Durasi bekerja karyawan tidak diperbolehkan kosong.',
            'durasi.numeric' => 'Durasi bekerja karyawan tidak diperbolehkan mengandung huruf.',
            'bukti_absensi.required' => 'Bukti foto presensi karyawan tidak diperbolehkan kosong.',
            'bukti_absensi.image' => 'Bukti foto presensi karyawan harus berupa gambar.',
            'bukti_absensi.mimes' => 'Bukti foto presensi karyawan harus berupa file ekstensi jpg, jpeg, atau png.',
            'bukti_absensi.max' => 'Ukuran bukti fotopresensi karyawan  tidak boleh lebih dari 2MB.',
            'latitude.required' => 'Titik latitude presensi karyawan tidak diperbolehkan kosong.',
            'longtitude.required' => 'Titik longitude presensi karyawan tidak diperbolehkan kosong.',
            'absensi.required' => 'Jenis presensi karyawan tidak diperbolehkan kosong.',
            'absensi.string' => 'Jenis presensi karyawan tidak diperbolehkan mengandung angka.',
            'kategori.required' => 'Kategori presensi karyawan tidak diperbolehkan kosong.',
        ];
    }

    public function model(array $row)
    {
        $user_id = $this->User->where('nama', $row['nama'])->first();
        $jadwal_id = $this->Shift->where('nama', $row['shift'])->first();
        $unit_kerja_id = $this->UnitKerja->where('nama_unit', $row['unit_kerja'])->first();

        // Get the data karyawan id that matches the unit kerja
        $data_karyawan_id = DataKaryawan::where('unit_kerja_id', $unit_kerja_id->id)->first();

        // Simpan gambar ke storage dan ambil path-nya
        $fotoPath = $this->saveImage($row['bukti_absensi']);

        return new Presensi([
            'user_id' => $user_id->id,
            'jadwal_id' => $jadwal_id->id,
            'data_karyawan_id' => $data_karyawan_id->id,
            'jam_masuk' => $row['jam_masuk'],
            'jam_keluar' => $row['jam_keluar'],
            'durasi' => $row['durasi'],
            'foto' => $fotoPath,
            'lat' => $row['latitude'],
            'long' => $row['longtitude'],
            'absensi' => $row['absensi'],
            'kategori' => $row['kategori']
        ]);
    }

    private function saveImage($image)
    {
        // Buat nama unik untuk file gambar
        $imageName = Str::uuid() . '.' . $image->getClientOriginalExtension();
        // Simpan gambar ke folder bukti_absensi di storage public
        $path = $image->storeAs('bukti_absensi', $imageName, 'public/images');
        return $path;
    }
}
