<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Seeder;
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
            'username' => 'super_admin',
            'data_completion_step' => 0,
            'status_akun' => 1,
            'password' => Hash::make('SAP_super_admin_password'),
        ]);
        $roleSuperAdmin->assignRole('Super Admin');
    }
}