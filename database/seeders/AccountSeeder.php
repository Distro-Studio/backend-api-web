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
            'name' => 'Olga Parks',
            'email' => 'olga@sa.rski.hosp',
            'username' => 'olgaP',
            'data_completion_step' => 1,
            'password' => Hash::make('password'),
        ]);
        $roleSuperAdmin->assignRole('Super Admin');

        // ! TESTER
        $roleKaryawan = User::create([
            'name' => 'Joyce Mills',
            'email' => 'joyce@adm.rski.hosp',
            'username' => 'joyceM',
            'data_completion_step' => 1,
            'password' => Hash::make('password'),
        ]);
        $roleKaryawan->assignRole('Karyawan');
    }
}
