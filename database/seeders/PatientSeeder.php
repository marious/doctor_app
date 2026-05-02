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
                'date_of_birth'   => '1998-04-15',
                'blood_group'     => 'A+',
                'marital_status'  => 'married',
                'address'         => '12 Nile St, Cairo',
                'risk_status'     => 'high_risk',
            ],
            [
                'name'            => 'Elena Rodriguez',
                'email'           => 'elena.rodriguez@example.com',
                'phone'           => '01011100002',
                'date_of_birth'   => '2002-08-22',
                'blood_group'     => 'O+',
                'marital_status'  => 'married',
                'address'         => '5 Tahrir Sq, Cairo',
                'risk_status'     => 'stable',
            ],
            [
                'name'            => 'Mia Thompson',
                'email'           => 'mia.thompson@example.com',
                'phone'           => '01011100003',
                'date_of_birth'   => '1995-01-10',
                'blood_group'     => 'B+',
                'marital_status'  => 'married',
                'address'         => '88 Corniche, Alexandria',
                'risk_status'     => 'stable',
            ],
            [
                'name'            => 'Sophia Davis',
                'email'           => 'sophia.davis@example.com',
                'phone'           => '01011100004',
                'date_of_birth'   => '1997-11-30',
                'blood_group'     => 'AB+',
                'marital_status'  => 'married',
                'address'         => '3 Pyramids Rd, Giza',
                'risk_status'     => 'monitor',
            ],
            [
                'name'            => 'Olivia Brown',
                'email'           => 'olivia.brown@example.com',
                'phone'           => '01011100005',
                'date_of_birth'   => '2000-06-18',
                'blood_group'     => 'O-',
                'marital_status'  => 'single',
                'address'         => '20 Zamalek, Cairo',
                'risk_status'     => 'stable',
            ],
            [
                'name'            => 'Emma Wilson',
                'email'           => 'emma.wilson@example.com',
                'phone'           => '01011100006',
                'date_of_birth'   => '2002-03-05',
                'blood_group'     => 'A-',
                'marital_status'  => 'single',
                'address'         => '7 Mohandessin, Cairo',
                'risk_status'     => 'stable',
            ],
            [
                'name'            => 'Nour Hassan',
                'email'           => 'nour.hassan@example.com',
                'phone'           => '01011100007',
                'date_of_birth'   => '1993-09-14',
                'blood_group'     => 'B-',
                'marital_status'  => 'married',
                'address'         => '15 Heliopolis, Cairo',
                'risk_status'     => 'monitor',
            ],
            [
                'name'            => 'Layla Ahmed',
                'email'           => 'layla.ahmed@example.com',
                'phone'           => '01011100008',
                'date_of_birth'   => '1991-12-27',
                'blood_group'     => 'A+',
                'marital_status'  => 'married',
                'address'         => '9 Maadi, Cairo',
                'risk_status'     => 'stable',
            ],
            [
                'name'            => 'Rania Mostafa',
                'email'           => 'rania.mostafa@example.com',
                'phone'           => '01011100009',
                'date_of_birth'   => '1994-07-08',
                'blood_group'     => 'O+',
                'marital_status'  => 'single',
                'address'         => '33 Nasr City, Cairo',
                'risk_status'     => 'high_risk',
            ],
            [
                'name'            => 'Dina Farouk',
                'email'           => 'dina.farouk@example.com',
                'phone'           => '01011100010',
                'date_of_birth'   => '1989-02-19',
                'blood_group'     => 'AB-',
                'marital_status'  => 'married',
                'address'         => '6 October City, Giza',
                'risk_status'     => 'stable',
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
