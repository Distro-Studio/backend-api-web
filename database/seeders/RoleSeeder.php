<?php

namespace Database\Seeders;

use Carbon\Carbon;
use Illuminate\Database\Seeder;
use Spatie\Permission\Models\Role;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;

class RoleSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $created_at = Carbon::now()->subDays(rand(0, 365));
        $updated_at = Carbon::now();

        $roleSuperAdmin = Role::create([
            'name' => 'Super Admin',
            'deskripsi' => 'drew between importance against attention cookies change tool rhythm merely twelve draw remember pipe handsome policeman mixture hay industrial birthday front himself iron declared',
            'created_at' => $created_at,
            'updated_at' => $updated_at,
        ]);

        Role::create([
            'name' => 'Direktur',
            'deskripsi' => 'provide car sharp pen shall deep owner industry zoo rate eager from tall sudden lamp verb prevent climate silence apart little declared mile gone',
            'created_at' => $created_at,
            'updated_at' => $updated_at,
        ]);

        Role::create([
            'name' => 'Admin',
            'deskripsi' => 'satellites native some bottle blanket extra continued young married lost far great door short quick example tin teeth variety shadow does line met these',
            'created_at' => $created_at,
            'updated_at' => $updated_at,
        ]);

        Role::create([
            'name' => 'Karyawan',
            'deskripsi' => 'recently cream related duty negative spring struck carbon saddle labor damage return court tide blue tea complex foot zoo broken clean been complete conversation',
            'created_at' => $created_at,
            'updated_at' => $updated_at,
        ]);

        $roleSuperAdmin->givePermissionTo([
            // verifikasi
            'verifikasi1 berkas',
            'view riwayatPerubahan',
            'verifikasi1 riwayatPerubahan',
            'view riwayatPerizinan',
            'verifikasi1 riwayatPerizinan',

            'create diklat',
            'view diklat',
            'export diklat',
            'verifikasi1 diklat',
            'verifikasi2 diklat',
            'verifikasi3 diklat',

            'create penggajianKaryawan',
            'edit penggajianKaryawan',
            'view penggajianKaryawan',
            'export penggajianKaryawan',
            'create thrKaryawan',
            'view thrKaryawan',
            'export thrKaryawan',

            'create jadwalKaryawan',
            'edit jadwalKaryawan',
            'delete jadwalKaryawan',
            'view jadwalKaryawan',
            'import jadwalKaryawan',
            'export jadwalKaryawan',
            'bypass jadwalKaryawan',
            'create tukarJadwal',
            'edit tukarJadwal',
            'delete tukarJadwal',
            'view tukarJadwal',
            'export tukarJadwal',
            'verifikasi1 tukarJadwal',
            'verifikasi2 tukarJadwal',
            'create lemburKaryawan',
            'edit lemburKaryawan',
            'delete lemburKaryawan',
            'view lemburKaryawan',
            'export lemburKaryawan',
            'create cutiKaryawan',
            'edit cutiKaryawan',
            'delete cutiKaryawan',
            'view cutiKaryawan',
            'export cutiKaryawan',
            'verifikasi1 cutiKaryawan',
            'verifikasi2 cutiKaryawan',

            'view presensiKaryawan',
            'import presensiKaryawan',
            'export presensiKaryawan',

            'verifikasi verifikator1',
            'verifikasi verifikator2',
            'create pengumuman',
            'edit pengumuman',
            'delete pengumuman',
            'view pengumuman',

            'create dataKaryawan',
            'edit dataKaryawan',
            'view dataKaryawan',
            'import dataKaryawan',
            'export dataKaryawan',

            'create role',
            'edit role',
            'view role',
            'edit permission',
            'view permission',

            'create unitKerja',
            'edit unitKerja',
            'delete unitKerja',
            'view unitKerja',
            'create jabatan',
            'edit jabatan',
            'delete jabatan',
            'view jabatan',
            'create kompetensi',
            'edit kompetensi',
            'delete kompetensi',
            'view kompetensi',
            'create kelompokGaji',
            'edit kelompokGaji',
            'delete kelompokGaji',
            'view kelompokGaji',
            'create kuesioner',
            'edit kuesioner',
            'delete kuesioner',
            'view kuesioner',

            'create premi',
            'edit premi',
            'delete premi',
            'view premi',
            'create ter21',
            'edit ter21',
            'delete ter21',
            'view ter21',
            'edit jadwalGaji',
            'view jadwalGaji',
            'create thr',
            'edit thr',
            'delete thr',
            'view thr',

            'create shift',
            'edit shift',
            'delete shift',
            'view shift',
            'create hariLibur',
            'edit hariLibur',
            'delete hariLibur',
            'view hariLibur',
            'create cuti',
            'edit cuti',
            'delete cuti',
            'view cuti',
            'edit lokasiKantor',
            'view lokasiKantor',
            'create penilaianKaryawan',
            'view penilaianKaryawan',
            'edit penilaianKaryawan',
            'delete penilaianKaryawan',
            'export penilaianKaryawan'
        ]);
    }
}
