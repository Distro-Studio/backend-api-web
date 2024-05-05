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
        $kelolaUsers = [
            'create.user',
            'edit.user',
            'delete.user',
            'view.user',

            'create.data_karyawan',
            'edit.data_karyawan',
            'delete.data_karyawan',
            'view.data_karyawan',
        ];

        $kelolaMasterSettings = [
            // Akun
            'create.role',
            'edit.role',
            'delete.role',
            'view.role',
            'import.role',
            'export.role',

            // Karyawan
            'create.unitkerja',
            'edit.unitkerja',
            'delete.unitkerja',
            'view.unitkerja',
            'import.unitkerja',
            'export.unitkerja',

            'create.jabatan',
            'edit.jabatan',
            'delete.jabatan',
            'view.jabatan',
            'import.jabatan',
            'export.jabatan',

            'create.kompetensi',
            'edit.kompetensi',
            'delete.kompetensi',
            'view.kompetensi',
            'import.kompetensi',
            'export.kompetensi',

            'create.kelompokgaji',
            'edit.kelompokgaji',
            'delete.kelompokgaji',
            'view.kelompokgaji',
            'import.kelompokgaji',
            'export.kelompokgaji',

            // Finance
            'create.premi',
            'edit.premi',
            'delete.premi',
            'view.premi',
            'import.premi',
            'export.premi',

            'create.ter21',
            'edit.ter21',
            'delete.ter21',
            'view.ter21',
            'import.ter21',
            'export.ter21',

            'create.jadwalPenggajian',
            'reset.jadwalPenggajian',

            'create.runTHR',
            'edit.runTHR',
            'delete.runTHR',
            'view.runTHR',
            'import.runTHR',
            'export.runTHR',
        ];

        foreach ($kelolaMasterSettings as $kelolaMasterSetting) {
            Permission::create(['name' => $kelolaMasterSetting]);
        }
    }
}
