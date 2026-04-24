<?php

namespace Database\Seeders;

use Carbon\Carbon;
use Illuminate\Database\Seeder;
use Modules\Patient\Models\HealthStat;
use Modules\Patient\Models\PatientTracking;

class HealthStatSeeder extends Seeder
{
    public function run(): void
    {
        // Find any pregnancy tracking record to attach stats to
        $trackings = PatientTracking::all();

        if ($trackings->isEmpty()) {
            $this->command->warn('No PatientTracking records found. Run PatientTrackingSeeder first.');
            return;
        }

        foreach ($trackings as $tracking) {
            // Skip if stats already exist
            if (HealthStat::where('patient_tracking_id', $tracking->id)->exists()) {
                continue;
            }

            if ($tracking->tracking_type === 'pregnancy') {
                $this->seedPregnancyStats($tracking);
            } else {
                $this->seedMenstrualStats($tracking);
            }
        }

        $this->command->info('HealthStat seeder completed.');
    }

    private function seedPregnancyStats(PatientTracking $tracking): void
    {
        // Baseline (pre-pregnancy or first visit) weight
        $baseWeight = 63.0;

        // Weekly weight gain progression (approx 0.45kg/week after week 12)
        $weeks = $tracking->gestationalWeeks() ?? 24;
        $entries = [];

        for ($w = 1; $w <= $weeks; $w += 2) {
            $gain = $w > 12 ? ($w - 12) * 0.42 : 0;
            $weight = round($baseWeight + $gain + (rand(-3, 3) / 10), 1);
            $bpm    = rand(72, 85);

            $gainSoFar     = $weight - $baseWeight;
            $expectedMin   = $w > 12 ? ($w - 12) * 0.36 : 0;
            $expectedMax   = $w > 12 ? ($w - 12) * 0.54 : 1;
            $weightStatus  = $gainSoFar < $expectedMin ? 'underweight'
                           : ($gainSoFar > $expectedMax ? 'overweight' : 'healthy_gain');

            $entries[] = [
                'patient_tracking_id' => $tracking->id,
                'weight_kg'           => $weight,
                'bpm'                 => $bpm,
                'weight_status'       => $weightStatus,
                'recorded_at'         => $tracking->lmp_date?->copy()->addWeeks($w) ?? now()->subWeeks($weeks - $w),
                'created_at'          => now(),
                'updated_at'          => now(),
            ];
        }

        HealthStat::insert($entries);
    }

    private function seedMenstrualStats(PatientTracking $tracking): void
    {
        HealthStat::insert([
            [
                'patient_tracking_id' => $tracking->id,
                'weight_kg'           => round(rand(550, 750) / 10, 1),
                'bpm'                 => rand(65, 80),
                'weight_status'       => null,
                'recorded_at'         => now()->subDays(7),
                'created_at'          => now(),
                'updated_at'          => now(),
            ],
            [
                'patient_tracking_id' => $tracking->id,
                'weight_kg'           => round(rand(550, 750) / 10, 1),
                'bpm'                 => rand(65, 80),
                'weight_status'       => null,
                'recorded_at'         => now(),
                'created_at'          => now(),
                'updated_at'          => now(),
            ],
        ]);
    }
}