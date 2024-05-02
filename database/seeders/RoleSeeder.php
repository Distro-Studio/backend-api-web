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

        Role::create([
            'name' => 'Super Admin',
            'description' => 'drew between importance against attention cookies change tool rhythm merely twelve draw remember pipe handsome policeman mixture hay industrial birthday front himself iron declared',
            'created_at' => $created_at,
            'updated_at' => $updated_at,
        ]);

        Role::create([
            'name' => 'Direktur',
            'description' => 'provide car sharp pen shall deep owner industry zoo rate eager from tall sudden lamp verb prevent climate silence apart little declared mile gone',
            'created_at' => $created_at,
            'updated_at' => $updated_at,
        ]);
        
        Role::create([
            'name' => 'Admin',
            'description' => 'satellites native some bottle blanket extra continued young married lost far great door short quick example tin teeth variety shadow does line met these',
            'created_at' => $created_at,
            'updated_at' => $updated_at,
        ]);

        Role::create([
            'name' => 'Karyawan',
            'description' => 'recently cream related duty negative spring struck carbon saddle labor damage return court tide blue tea complex foot zoo broken clean been complete conversation',
            'created_at' => $created_at,
            'updated_at' => $updated_at,
        ]);

        // $roleDirektur->givePermissionTo([
        //     'create.unitkerja',
        //     'edit.unitkerja',
        //     'delete.unitkerja',
        //     'view.unitkerja',

        //     'create.jabatan',
        //     'edit.jabatan',
        //     'delete.jabatan',
        //     'view.jabatan',

        //     'create.profesi',
        //     'edit.profesi',
        //     'delete.profesi',
        //     'view.profesi',

        //     'create.kelompokgaji',
        //     'edit.kelompokgaji',
        //     'delete.kelompokgaji',
        //     'view.kelompokgaji',

        //     'create.data_karyawan',
        //     'edit.data_karyawan',
        //     'delete.data_karyawan',
        //     'view.data_karyawan',
        // ]);

        // $roleAdmin->givePermissionTo([
        //     'create.user',
        //     'edit.user',
        //     'view.user',
        // ]);

        // $roleKaryawan->givePermissionTo([
        //     'view.kelompokgaji',
        //     'view.user',
        //     'edit.data_karyawan',
        // ]);
    }
}
