<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Modules\Patient\Models\Symptom;

class SymptomSeeder extends Seeder
{
    public function run(): void
    {
        $symptoms = [
            // General Symptoms
            ['key' => 'cramps',            'label' => 'Cramps',            'group' => 'general',           'sort_order' => 1],
            ['key' => 'fatigue',           'label' => 'Fatigue',           'group' => 'general',           'sort_order' => 2],
            ['key' => 'headache',          'label' => 'Headache',          'group' => 'general',           'sort_order' => 3],
            ['key' => 'bloating',          'label' => 'Bloating',          'group' => 'general',           'sort_order' => 4],
            ['key' => 'backache',          'label' => 'Backache',          'group' => 'general',           'sort_order' => 5],
            ['key' => 'nausea',            'label' => 'Nausea',            'group' => 'general',           'sort_order' => 6],
            ['key' => 'breast_tenderness', 'label' => 'Breast Tenderness', 'group' => 'general',           'sort_order' => 7],
            ['key' => 'acne',              'label' => 'Acne',              'group' => 'general',           'sort_order' => 8],

            // Mood
            ['key' => 'mood_swings',       'label' => 'Mood Swings',       'group' => 'mood',              'sort_order' => 1],
            ['key' => 'anxiety',           'label' => 'Anxiety',           'group' => 'mood',              'sort_order' => 2],
            ['key' => 'irritability',      'label' => 'Irritability',      'group' => 'mood',              'sort_order' => 3],
            ['key' => 'depression',        'label' => 'Depression',        'group' => 'mood',              'sort_order' => 4],

            // Vaginal Discharge
            ['key' => 'spotting',          'label' => 'Spotting',          'group' => 'vaginal_discharge', 'sort_order' => 1],
            ['key' => 'light_discharge',   'label' => 'Light Discharge',   'group' => 'vaginal_discharge', 'sort_order' => 2],
            ['key' => 'heavy_discharge',   'label' => 'Heavy Discharge',   'group' => 'vaginal_discharge', 'sort_order' => 3],
            ['key' => 'unusual_discharge', 'label' => 'Unusual Discharge', 'group' => 'vaginal_discharge', 'sort_order' => 4],
        ];

        foreach ($symptoms as $symptom) {
            Symptom::updateOrCreate(['key' => $symptom['key']], $symptom);
        }
    }
}
