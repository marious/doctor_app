<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Modules\Users\Models\User;

class AssistantSeeder extends Seeder
{
    public function run(): void
    {
        User::firstOrCreate(
            ['email' => 'assistant@hercare.com'],
            [
                'name'     => 'Clinic Assistant',
                'password' => bcrypt('password987'),
                'role_id'  => 3,
                'phone'    => '111111111',
                'active'   => 1,
            ]
        );
    }
}
