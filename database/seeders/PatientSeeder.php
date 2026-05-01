<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;
use Modules\Users\Models\User;

class PatientSeeder extends Seeder
{
    public function run(): void
    {
        $patients = [
            [
                'name'            => 'Sarah Jenkins',
                'email'           => 'sarah.jenkins@example.com',
                'phone'           => '01011100001',
                'date_of_birth'   => '1992-04-15',
                'blood_group'     => 'A+',
                'marital_status'  => 'married',
                'address'         => '12 Nile St, Cairo',
            ],
            [
                'name'            => 'Elena Rodriguez',
                'email'           => 'elena.rodriguez@example.com',
                'phone'           => '01011100002',
                'date_of_birth'   => '1995-08-22',
                'blood_group'     => 'O+',
                'marital_status'  => 'married',
                'address'         => '5 Tahrir Sq, Cairo',
            ],
            [
                'name'            => 'Chloe Miller',
                'email'           => 'chloe.miller@example.com',
                'phone'           => '01011100003',
                'date_of_birth'   => '1990-01-10',
                'blood_group'     => 'B+',
                'marital_status'  => 'single',
                'address'         => '88 Corniche, Alexandria',
            ],
            [
                'name'            => 'Grace Lee',
                'email'           => 'grace.lee@example.com',
                'phone'           => '01011100004',
                'date_of_birth'   => '1988-11-30',
                'blood_group'     => 'AB+',
                'marital_status'  => 'married',
                'address'         => '3 Pyramids Rd, Giza',
            ],
            [
                'name'            => 'Isabella Garcia',
                'email'           => 'isabella.garcia@example.com',
                'phone'           => '01011100005',
                'date_of_birth'   => '1997-06-18',
                'blood_group'     => 'O-',
                'marital_status'  => 'single',
                'address'         => '20 Zamalek, Cairo',
            ],
            [
                'name'            => 'Nour Hassan',
                'email'           => 'nour.hassan@example.com',
                'phone'           => '01011100006',
                'date_of_birth'   => '1993-03-05',
                'blood_group'     => 'A-',
                'marital_status'  => 'married',
                'address'         => '7 Mohandessin, Cairo',
            ],
            [
                'name'            => 'Layla Ahmed',
                'email'           => 'layla.ahmed@example.com',
                'phone'           => '01011100007',
                'date_of_birth'   => '1996-09-14',
                'blood_group'     => 'B-',
                'marital_status'  => 'married',
                'address'         => '15 Heliopolis, Cairo',
            ],
            [
                'name'            => 'Mona Ibrahim',
                'email'           => 'mona.ibrahim@example.com',
                'phone'           => '01011100008',
                'date_of_birth'   => '1991-12-27',
                'blood_group'     => 'A+',
                'marital_status'  => 'married',
                'address'         => '9 Maadi, Cairo',
            ],
            [
                'name'            => 'Rania Mostafa',
                'email'           => 'rania.mostafa@example.com',
                'phone'           => '01011100009',
                'date_of_birth'   => '1994-07-08',
                'blood_group'     => 'O+',
                'marital_status'  => 'single',
                'address'         => '33 Nasr City, Cairo',
            ],
            [
                'name'            => 'Dina Farouk',
                'email'           => 'dina.farouk@example.com',
                'phone'           => '01011100010',
                'date_of_birth'   => '1989-02-19',
                'blood_group'     => 'AB-',
                'marital_status'  => 'married',
                'address'         => '6 October City, Giza',
            ],
        ];

        foreach ($patients as $data) {
            User::firstOrCreate(
                ['email' => $data['email']],
                array_merge($data, [
                    'password' => Hash::make('password'),
                    'role_id'  => 2,
                    'active'   => true,
                ])
            );
        }
    }
}
