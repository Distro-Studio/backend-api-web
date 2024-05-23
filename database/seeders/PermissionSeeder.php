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
            // Karyawan
            'User' => ['create user', 'edit user', 'delete user', 'view user', 'import user', 'export user'],
            'Data Karyawan' => ['create dataKaryawan', 'edit dataKaryawan', 'delete dataKaryawan', 'view dataKaryawan', 'import dataKaryawan', 'export dataKaryawan'],

            // Master setting
            'Role' => ['create role', 'edit role', 'delete role', 'view role', 'import role', 'export role'],
            'Permission' => ['create permission', 'edit permission', 'delete permission', 'view permission'],

            'Unit Kerja' => ['create unitKerja', 'edit unitKerja', 'delete unitKerja', 'view unitKerja', 'import unitKerja', 'export unitKerja'],
            'Jabatan' => ['create jabatan', 'edit jabatan', 'delete jabatan', 'view jabatan', 'import jabatan', 'export jabatan'],
            'Kompetensi' => ['create kompetensi', 'edit kompetensi', 'delete kompetensi', 'view kompetensi', 'import kompetensi', 'export kompetensi'],
            'Kelompok Gaji' => ['create kelompokGaji', 'edit kelompokGaji', 'delete kelompokGaji', 'view kelompokGaji', 'import kelompokGaji', 'export kelompokGaji'],

            'Premi' => ['create premi', 'edit premi', 'delete premi', 'view premi', 'import premi', 'export premi'],
            'TER21' => ['create ter21', 'edit ter21', 'delete ter21', 'view ter21', 'import ter21', 'export ter21'],
            'Jadwal Penggajian' => ['create jadwalGaji', 'reset jadwalGaji'],
            'THR' => ['create thr', 'edit thr', 'delete thr', 'view thr'],

            'Shift' => ['create shift', 'edit shift', 'delete shift', 'view shift', 'import shift', 'export shift'],
            'Hari Libur' => ['create hariLibur', 'edit hariLibur', 'delete hariLibur', 'view hariLibur', 'import hariLibur', 'export hariLibur'],
            'Cuti' => ['create cuti', 'edit cuti', 'delete cuti', 'view cuti', 'import cuti', 'export cuti'],
        ];

        foreach ($permissions as $group => $perms) {
            foreach ($perms as $permission) {
                Permission::create(['name' => $permission, 'group' => $group]);
            }
        }
    }
}
