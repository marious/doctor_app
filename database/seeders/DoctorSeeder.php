<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Modules\Users\Models\User;

class DoctorSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
         User::create([
            'name'      => 'Admin User',
            'email'     => 'admin@admin.com',
            'password'  => bcrypt('password987'),
            'role_id'   => 1,
            'phone'     => '000000000',
            'active'    => 1,
        ]);
    }
}
