<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;
use Modules\ServiceRegistrations\Models\PatientServicePayment;
use Modules\ServiceRegistrations\Models\PatientServiceRegistration;

class MergeDuplicateServiceRegistrations extends Command
{
    protected $signature   = 'registrations:merge-duplicates {--dry-run : Preview changes without saving}';
    protected $description = 'Merge duplicate service registrations that share the same patient_id, service_id, and service_date';

    public function handle(): int
    {
        $dryRun = $this->option('dry-run');

        if ($dryRun) {
            $this->warn('DRY RUN — no changes will be saved.');
        }

        // Find all groups with more than one registration for the same patient+service+date
        $duplicateGroups = PatientServiceRegistration::select('patient_id', 'service_id', 'service_date')
            ->groupBy('patient_id', 'service_id', 'service_date')
            ->havingRaw('COUNT(*) > 1')
            ->get();

        if ($duplicateGroups->isEmpty()) {
            $this->info('No duplicate registrations found.');
            return self::SUCCESS;
        }

        $this->info("Found {$duplicateGroups->count()} duplicate group(s).");
        $this->newLine();

        $totalMerged  = 0;
        $totalDeleted = 0;

        foreach ($duplicateGroups as $group) {
            $registrations = PatientServiceRegistration::where('patient_id', $group->patient_id)
                ->where('service_id', $group->service_id)
                ->whereDate('service_date', $group->service_date)
                ->orderBy('id')
                ->get();

            $master     = $registrations->first();
            $duplicates = $registrations->slice(1);

            $mergedAmountPaid = $registrations->sum('amount_paid');
            $duplicateIds     = $duplicates->pluck('id')->toArray();

            $this->line("  Patient #{$master->patient_id} — Service #{$master->service_id} — Date: {$master->service_date->toDateString()}");
            $this->line("    Master ID : #{$master->id}  (amount_paid: {$master->amount_paid} → {$mergedAmountPaid})");
            $this->line("    Duplicates: #" . implode(', #', $duplicateIds));

            $paymentsMoved = PatientServicePayment::whereIn('registration_id', $duplicateIds)->count();
            $this->line("    Payments to re-point: {$paymentsMoved}");
            $this->newLine();

            if (!$dryRun) {
                DB::transaction(function () use ($master, $duplicateIds, $mergedAmountPaid) {
                    // Re-point all payments from duplicates to master
                    PatientServicePayment::whereIn('registration_id', $duplicateIds)
                        ->update(['registration_id' => $master->id]);

                    // Set master amount_paid to sum of all duplicates
                    $master->update(['amount_paid' => $mergedAmountPaid]);

                    // Delete the duplicate registrations
                    PatientServiceRegistration::whereIn('id', $duplicateIds)->delete();
                });
            }

            $totalMerged++;
            $totalDeleted += count($duplicateIds);
        }

        if ($dryRun) {
            $this->warn("DRY RUN complete — {$totalMerged} group(s) would be merged, {$totalDeleted} duplicate(s) would be deleted.");
        } else {
            $this->info("Done — {$totalMerged} group(s) merged, {$totalDeleted} duplicate record(s) deleted.");
        }

        return self::SUCCESS;
    }
}
