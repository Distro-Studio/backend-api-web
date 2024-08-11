<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Spatie\Permission\Models\Permission;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;

class PermissionSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $permissions = [
            // Perusahaan
            'Diklat' => ['create diklat', 'view diklat', 'export diklat'],
            'Pelaporan Karyawan' => ['view pelaporanKaryawan', 'export pelaporanKaryawan'],
            'Penilaian Karyawan' => ['view penilaianKaryawan', 'export penilaianKaryawan'],

            // Keuangan
            'Penggajian THR Karyawan' => ['create thrKaryawan', 'view thrKaryawan', 'export thrKaryawan'],
            'Penggajian Karyawan' => ['create penggajianKaryawan', 'edit penggajianKaryawan', 'view penggajianKaryawan', 'delete penggajianKaryawan', 'import penggajianKaryawan', 'export penggajianKaryawan'],

            // Jadwals
            'Jadwal Karyawan' => ['create jadwalKaryawan', 'edit jadwalKaryawan', 'delete jadwalKaryawan', 'view jadwalKaryawan', 'import jadwalKaryawan', 'export jadwalKaryawan'],
            'Jadwal Tukar Karyawan' => ['create tukarJadwal', 'edit tukarJadwal', 'delete tukarJadwal', 'view tukarJadwal', 'import tukarJadwal', 'export tukarJadwal'],
            'Jadwal Lembur Karyawan' => ['create lemburKaryawan', 'edit lemburKaryawan', 'delete lemburKaryawan', 'view lemburKaryawan', 'export lemburKaryawan'],
            'Jadwal Cuti Karyawan' => ['create cutiKaryawan', 'edit cutiKaryawan', 'delete cutiKaryawan', 'view cutiKaryawan', 'export cutiKaryawan'],

            // Presensi
            'Presensi Karyawan' => ['create presensiKaryawan', 'edit presensiKaryawan', 'delete presensiKaryawan', 'view presensiKaryawan', 'import presensiKaryawan', 'export presensiKaryawan'],

            // Karyawan
            'Karyawan Data' => ['create dataKaryawan', 'edit dataKaryawan', 'delete dataKaryawan', 'view dataKaryawan', 'import dataKaryawan', 'export dataKaryawan'],

            // Dashboard
            'Pengumuman' => ['create pengumuman', 'edit pengumuman', 'delete pengumuman', 'view pengumuman'],
            'Notifikasi' => ['create notifikasi', 'edit notifikasi', 'delete notifikasi', 'view notifikasi'],

            // Master setting
            'Verifikasi Data' => ['verifikasi data', 'verifikasi verifikator1', 'verifikasi verifikator2'],
            'Pengaturan Role' => ['create role', 'edit role', 'delete role', 'view role', 'import role', 'export role'],
            'Pengaturan Permission' => ['create permission', 'edit permission', 'delete permission', 'view permission'],
            'Pengaturan Unit Kerja' => ['create unitKerja', 'edit unitKerja', 'delete unitKerja', 'view unitKerja', 'import unitKerja', 'export unitKerja'],
            'Pengaturan Jabatan' => ['create jabatan', 'edit jabatan', 'delete jabatan', 'view jabatan', 'import jabatan', 'export jabatan'],
            'Pengaturan Kompetensi' => ['create kompetensi', 'edit kompetensi', 'delete kompetensi', 'view kompetensi', 'import kompetensi', 'export kompetensi'],
            'Pengaturan Kelompok Gaji' => ['create kelompokGaji', 'edit kelompokGaji', 'delete kelompokGaji', 'view kelompokGaji', 'import kelompokGaji', 'export kelompokGaji'],
            'Pengaturan Kuesioner' => ['create kuesioner', 'edit kuesioner', 'delete kuesioner', 'view kuesioner', 'import kuesioner', 'export kuesioner'],
            'Pengaturan Premi' => ['create premi', 'edit premi', 'delete premi', 'view premi', 'import premi', 'export premi'],
            'Pengaturan TER21' => ['create ter21', 'edit ter21', 'delete ter21', 'view ter21', 'import ter21', 'export ter21'],
            'Pengaturan Jadwal Penggajian' => ['create jadwalGaji', 'view jadwalGaji'],
            'Pengaturan THR' => ['create thr', 'edit thr', 'delete thr', 'view thr'],
            'Pengaturan Shift' => ['create shift', 'edit shift', 'delete shift', 'view shift', 'import shift', 'export shift'],
            'Pengaturan Hari Libur' => ['create hariLibur', 'edit hariLibur', 'delete hariLibur', 'view hariLibur', 'import hariLibur', 'export hariLibur'],
            'Pengaturan Cuti' => ['create cuti', 'edit cuti', 'delete cuti', 'view cuti', 'import cuti', 'export cuti'],
            'Pengaturan Lokasi Kantor' => ['edit lokasiKantor', 'view lokasiKantor'],
        ];

        foreach ($permissions as $group => $perms) {
            foreach ($perms as $permission) {
                Permission::create(['name' => $permission, 'group' => $group]);
            }
        }
    }
}
