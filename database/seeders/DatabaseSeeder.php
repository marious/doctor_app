<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    use WithoutModelEvents;

    public function run(): void
    {
        $this->call([
            DoctorSeeder::class,
            AssistantSeeder::class,
            SymptomSeeder::class,
            PatientSeeder::class,
            AppointmentSeeder::class,
            PatientSessionSeeder::class,
            ClinicServiceCategorySeeder::class,
            ArticleSeeder::class,
            VideoSeeder::class,
        ]);
    }
}
