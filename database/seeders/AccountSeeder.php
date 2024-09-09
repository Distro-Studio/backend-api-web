<?php

namespace Database\Seeders;

use App\Models\DataKaryawan;
use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;

class AccountSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $roleSuperAdmin = User::create([
            'nama' => 'Super Admin',
            'username' => 'super.admin',
            'data_completion_step' => 0,
            'status_aktif' => 2,
            'password' => Hash::make('SAP_super_admin_password'),
        ]);

        DataKaryawan::create([
            'user_id' => $roleSuperAdmin->id,
            'email' => 'super_admin@admin.rski',
        ]);
        $roleSuperAdmin->assignRole('Super Admin');
    }
}
