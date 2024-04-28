<?php

namespace Database\Seeders;

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
        Role::create(['name' => 'Super Admin']);
        $roleDirektur = Role::create(['name' => 'Direktur']);
        $roleAdmin = Role::create(['name' => 'Admin']);
        $roleKaryawan = Role::create(['name' => 'Karyawan']);

        $roleDirektur->givePermissionTo([
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

            'create.data_karyawan',
            'edit.data_karyawan',
            'delete.data_karyawan',
            'view.data_karyawan',
        ]);

        $roleAdmin->givePermissionTo([
            'create.user',
            'edit.user',
            'view.user',
        ]);

        $roleKaryawan->givePermissionTo([
            'view.kelompokgaji',
            'view.user',
            'edit.data_karyawan',
        ]);
    }
}
