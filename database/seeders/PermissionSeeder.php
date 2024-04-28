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
            'create.role',
            'edit.role',
            'delete.role',
            'view.role',

            'create.unitkerja',
            'edit.unitkerja',
            'delete.unitkerja',
            'view.unitkerja',

            'create.jabatan',
            'edit.jabatan',
            'delete.jabatan',
            'view.jabatan',

            'create.profesi',
            'edit.profesi',
            'delete.profesi',
            'view.profesi',

            'create.kelompokgaji',
            'edit.kelompokgaji',
            'delete.kelompokgaji',
            'view.kelompokgaji',

            'create.user',
            'edit.user',
            'delete.user',
            'view.user',

            'create.data_karyawan',
            'edit.data_karyawan',
            'delete.data_karyawan',
            'view.data_karyawan',
        ];

        foreach ($permissions as $permission) {
            Permission::create(['name' => $permission]);
        }
    }
}
