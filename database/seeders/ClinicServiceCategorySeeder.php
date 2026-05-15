<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Modules\Services\Models\ClinicService;
use Modules\Services\Models\ClinicServiceCategory;

class ClinicServiceCategorySeeder extends Seeder
{
    public function run(): void
    {
        $categories = [
            'Pregnancy Consultation',
            'Gynecology Consultation',
            'Ultrasound',
            'Follow-up Session',
            'Postpartum Care',
            'Fertility Treatment',
            'General Consultation',
        ];

        foreach ($categories as $name) {
            ClinicServiceCategory::firstOrCreate(['name' => $name]);
        }

        // Sample services matching the Figma
        $samples = [
            [
                'category' => 'Pregnancy Consultation',
                'name'        => 'First Trimester Checkup',
                'description' => 'Comprehensive',
                'price'       => 500,
                'is_package'  => false,
                'is_active'   => true,
            ],
            [
                'category' => 'Gynecology Consultation',
                'name'        => 'Routine Gynecology Exam',
                'description' => 'Annual wellness',
                'price'       => 400,
                'is_package'  => true,
                'is_active'   => true,
            ],
            [
                'category' => 'Ultrasound',
                'name'        => '4D Ultrasound',
                'description' => 'Advanced 4D',
                'price'       => 600,
                'is_package'  => false,
                'is_active'   => true,
            ],
            [
                'category' => 'Follow-up Session',
                'name'        => 'Postpartum Follow-up',
                'description' => 'Post-delivery',
                'price'       => 350,
                'is_package'  => false,
                'is_active'   => false,
            ],
        ];

        foreach ($samples as $item) {
            $category = ClinicServiceCategory::where('name', $item['category'])->first();
            ClinicService::firstOrCreate(
                ['name' => $item['name']],
                [
                    'category_id' => $category->id,
                    'description' => $item['description'],
                    'price'       => $item['price'],
                    'is_package'  => $item['is_package'],
                    'is_active'   => $item['is_active'],
                ]
            );
        }
    }
}
