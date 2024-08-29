<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Spatie\Permission\Models\Permission;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;

class PermissionSeeder extends Seeder
{
    public function run(): void
    {
        $permissions = [
            // Verifikasi
            'Berkas' => ['verifikasi1 berkas'],
            'Riwayat Perubahan' => ['view riwayatPerubahan', 'verifikasi1 riwayatPerubahan'],
            'Riwayat Izin' => ['view riwayatPerizinan', 'export riwayatPerizinan', 'verifikasi1 riwayatPerizinan'],

            // Perusahaan
            'Diklat' => ['create diklat', 'view diklat', 'export diklat', 'verifikasi1 diklat', 'verifikasi2 diklat'],

            // Keuangan
            'Penggajian THR Karyawan' => ['create thrKaryawan', 'view thrKaryawan', 'export thrKaryawan'],
            'Penggajian Karyawan' => ['create penggajianKaryawan', 'edit penggajianKaryawan', 'view penggajianKaryawan',   'export penggajianKaryawan'],

            // Jadwals
            'Jadwal Karyawan' => ['create jadwalKaryawan', 'edit jadwalKaryawan', 'delete jadwalKaryawan', 'view jadwalKaryawan', 'import jadwalKaryawan', 'export jadwalKaryawan', 'bypass jadwalKaryawan'],
            'Jadwal Tukar Karyawan' => ['create tukarJadwal', 'edit tukarJadwal', 'delete tukarJadwal', 'view tukarJadwal',  'export tukarJadwal', 'verifikasi1 tukarJadwal', 'verifikasi2 tukarJadwal'],
            'Jadwal Lembur Karyawan' => ['create lemburKaryawan', 'edit lemburKaryawan', 'delete lemburKaryawan', 'view lemburKaryawan', 'export lemburKaryawan'],
            'Jadwal Cuti Karyawan' => ['create cutiKaryawan', 'edit cutiKaryawan', 'delete cutiKaryawan', 'view cutiKaryawan', 'export cutiKaryawan', 'verifikasi1 cutiKaryawan', 'verifikasi2 cutiKaryawan'],

            // Presensi
            'Presensi Karyawan' => ['view presensiKaryawan', 'import presensiKaryawan', 'export presensiKaryawan'],

            // Karyawan
            'Karyawan Data' => ['create dataKaryawan', 'edit dataKaryawan', 'view dataKaryawan', 'import dataKaryawan', 'export dataKaryawan'],

            // Dashboard
            'Pengumuman' => ['create pengumuman', 'edit pengumuman', 'delete pengumuman', 'view pengumuman'],

            // Master setting
            'Verifikasi Data' => ['verifikasi verifikator1', 'verifikasi verifikator2'],
            'Pengaturan Role' => ['create role', 'edit role', 'view role'],
            'Pengaturan Permission' => ['edit permission', 'delete permission', 'view permission'],
            'Pengaturan Unit Kerja' => ['create unitKerja', 'edit unitKerja', 'delete unitKerja', 'view unitKerja'],
            'Pengaturan Jabatan' => ['create jabatan', 'edit jabatan', 'delete jabatan', 'view jabatan'],
            'Pengaturan Kompetensi' => ['create kompetensi', 'edit kompetensi', 'delete kompetensi', 'view kompetensi'],
            'Pengaturan Kelompok Gaji' => ['create kelompokGaji', 'edit kelompokGaji', 'delete kelompokGaji', 'view kelompokGaji'],
            'Pengaturan Kuesioner' => ['create kuesioner', 'edit kuesioner', 'delete kuesioner', 'view kuesioner'],
            'Pengaturan Premi' => ['create premi', 'edit premi', 'delete premi', 'view premi'],
            'Pengaturan TER21' => ['create ter21', 'edit ter21', 'delete ter21', 'view ter21'],
            'Pengaturan Jadwal Penggajian' => ['edit jadwalGaji', 'view jadwalGaji'],
            'Pengaturan THR' => ['create thr', 'edit thr', 'delete thr', 'view thr'],
            'Pengaturan Shift' => ['create shift', 'edit shift', 'delete shift', 'view shift'],
            'Pengaturan Hari Libur' => ['create hariLibur', 'edit hariLibur', 'delete hariLibur', 'view hariLibur'],
            'Pengaturan Cuti' => ['create cuti', 'edit cuti', 'delete cuti', 'view cuti'],
            'Pengaturan Lokasi Kantor' => ['edit lokasiKantor', 'view lokasiKantor'],
            'Penilaian Karyawan' => ['create penilaianKaryawan', 'view penilaianKaryawan', 'edit penilaianKaryawan', 'delete penilaianKaryawan', 'export penilaianKaryawan'],
        ];

        foreach ($permissions as $group => $perms) {
            foreach ($perms as $permission) {
                Permission::create(['name' => $permission, 'group' => $group]);
            }
        }
    }
}
