<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Barryvdh\DomPDF\Facade\Pdf;
use Carbon\Carbon;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Modules\Appointments\Models\Appointment;
use Modules\Patient\Models\PatientTracking;
use Modules\Users\Models\User;

class DoctorReportsController extends Controller
{
    private const PERIOD_MAP = [
        '1m'  => 1,
        '3m'  => 3,
        '6m'  => 6,
        '1y'  => 12,
    ];

    private const MODE_LABELS = [
        'pregnancy' => 'Pregnancy',
        'menstrual' => 'Period Tracking',
        'menopause' => 'Menopause',
        'fertility' => 'Fertility',
    ];

    /**
     * GET /doctor/reports
     */
    public function index(Request $request): JsonResponse
    {
        $request->validate([
            'period' => ['nullable', 'in:1m,3m,6m,1y'],
        ]);

        $period   = $request->input('period', '6m');
        $months   = self::PERIOD_MAP[$period];
        $dateFrom = Carbon::now()->startOfMonth()->subMonths($months - 1);
        $dateTo   = Carbon::now()->endOfMonth();

        return response()->json([
            'success' => true,
            'data' => [
                'filters' => [
                    'period'    => $period,
                    'date_from' => $dateFrom->toDateString(),
                    'date_to'   => $dateTo->toDateString(),
                    'options'   => [
                        ['value' => '1m', 'label' => 'Last 1 Month'],
                        ['value' => '3m', 'label' => 'Last 3 Months'],
                        ['value' => '6m', 'label' => 'Last 6 Months'],
                        ['value' => '1y', 'label' => 'Last 1 Year'],
                    ],
                ],
                'summary'                  => $this->buildSummary($dateFrom, $dateTo),
                'patient_growth'           => $this->buildPatientGrowth($dateFrom, $dateTo, $months),
                'patient_mode_distribution' => $this->buildModeDistribution(),
            ],
        ]);
    }

    /**
     * GET /doctor/reports/export
     * Streams the report file directly for download.
     * Query params: period (1m|3m|6m|1y), format (pdf|csv, default pdf)
     */
    public function export(Request $request)
    {
        $request->validate([
            'period' => ['nullable', 'in:1m,3m,6m,1y'],
            'format' => ['nullable', 'in:pdf,csv'],
        ]);

        $period   = $request->input('period', '6m');
        $format   = $request->input('format', 'pdf');
        $months   = self::PERIOD_MAP[$period];
        $dateFrom = Carbon::now()->startOfMonth()->subMonths($months - 1);
        $dateTo   = Carbon::now()->endOfMonth();

        $growth       = $this->buildPatientGrowth($dateFrom, $dateTo, $months);
        $distribution = $this->buildModeDistribution();
        $summary      = $this->buildSummary($dateFrom, $dateTo);

        if ($format === 'csv') {
            return $this->downloadCsv($period, $growth, $distribution, $summary);
        }

        return $this->downloadPdf($period, $dateFrom, $dateTo, $growth, $distribution, $summary);
    }

    private function downloadPdf(
        string $period,
        Carbon $dateFrom,
        Carbon $dateTo,
        array  $growth,
        array  $distribution,
        array  $summary
    ) {
        $periodLabels = [
            '1m' => 'Last 1 Month',
            '3m' => 'Last 3 Months',
            '6m' => 'Last 6 Months',
            '1y' => 'Last 1 Year',
        ];

        $pdf = Pdf::loadView('reports.clinic_report', [
            'filters'      => [
                'date_from' => $dateFrom->format('M d, Y'),
                'date_to'   => $dateTo->format('M d, Y'),
            ],
            'periodLabel'  => $periodLabels[$period],
            'summary'      => $summary,
            'growth'       => $growth,
            'distribution' => $distribution,
        ])->setPaper('a4', 'portrait');

        $filename = 'clinic-report-' . $period . '-' . now()->format('Y-m-d') . '.pdf';

        return $pdf->download($filename);
    }

    private function downloadCsv(string $period, array $growth, array $distribution, array $summary)
    {
        $filename = 'clinic-report-' . $period . '-' . now()->format('Y-m-d') . '.csv';

        return response()->streamDownload(function () use ($growth, $distribution, $summary) {
            $out = fopen('php://output', 'w');

            fputcsv($out, ['=== Clinic Summary ===']);
            fputcsv($out, ['Total Patients', $summary['total_patients']]);
            fputcsv($out, ['Total Appointments', $summary['total_appointments']]);
            fputcsv($out, ['New Patients This Period', $summary['new_patients_this_period']]);
            fputcsv($out, []);

            fputcsv($out, ['=== Patient Growth ===']);
            fputcsv($out, ['Month', 'New Patients', 'Appointments']);
            foreach ($growth as $row) {
                fputcsv($out, [$row['label'] . ' ' . $row['year'], $row['patients'], $row['appointments']]);
            }
            fputcsv($out, []);

            fputcsv($out, ['=== Patient Mode Distribution ===']);
            fputcsv($out, ['Mode', 'Count', 'Percentage']);
            foreach ($distribution as $row) {
                fputcsv($out, [$row['label'], $row['count'], $row['percentage'] . '%']);
            }

            fclose($out);
        }, $filename, ['Content-Type' => 'text/csv']);
    }

    // ─── Private Builders ─────────────────────────────────────────────────────

    private function buildSummary(Carbon $dateFrom, Carbon $dateTo): array
    {
        $totalPatients = User::where('role_id', 2)->count();

        $newPatients = User::where('role_id', 2)
            ->whereBetween('created_at', [$dateFrom->startOfDay(), $dateTo->endOfDay()])
            ->count();

        $totalAppointments = Appointment::whereBetween('appointment_date', [
            $dateFrom->toDateString(),
            $dateTo->toDateString(),
        ])->count();

        return [
            'total_patients'          => $totalPatients,
            'new_patients_this_period' => $newPatients,
            'total_appointments'      => $totalAppointments,
        ];
    }

    private function buildPatientGrowth(Carbon $dateFrom, Carbon $dateTo, int $months): array
    {
        // Aggregate new patients per month (role_id=2 = patient)
        $patientsByMonth = User::where('role_id', 2)
            ->whereBetween('created_at', [$dateFrom->copy()->startOfDay(), $dateTo->copy()->endOfDay()])
            ->selectRaw('YEAR(created_at) as year, MONTH(created_at) as month, COUNT(*) as total')
            ->groupByRaw('YEAR(created_at), MONTH(created_at)')
            ->get()
            ->keyBy(fn($r) => "{$r->year}-{$r->month}");

        // Aggregate appointments per month
        $appointmentsByMonth = Appointment::whereBetween('appointment_date', [
                $dateFrom->toDateString(),
                $dateTo->toDateString(),
            ])
            ->selectRaw('YEAR(appointment_date) as year, MONTH(appointment_date) as month, COUNT(*) as total')
            ->groupByRaw('YEAR(appointment_date), MONTH(appointment_date)')
            ->get()
            ->keyBy(fn($r) => "{$r->year}-{$r->month}");

        $result = [];
        $cursor = $dateFrom->copy()->startOfMonth();

        for ($i = 0; $i < $months; $i++) {
            $key = "{$cursor->year}-{$cursor->month}";
            $result[] = [
                'month'        => (int) $cursor->month,
                'year'         => (int) $cursor->year,
                'label'        => $cursor->format('M'),
                'patients'     => (int) ($patientsByMonth[$key]->total ?? 0),
                'appointments' => (int) ($appointmentsByMonth[$key]->total ?? 0),
            ];
            $cursor->addMonth();
        }

        return $result;
    }

    private function buildModeDistribution(): array
    {
        // Get the latest tracking record per patient
        $counts = PatientTracking::selectRaw('tracking_type, COUNT(DISTINCT patient_id) as total')
            ->groupBy('tracking_type')
            ->get()
            ->keyBy('tracking_type');

        $total = $counts->sum('total');

        $rows = [];
        foreach (self::MODE_LABELS as $mode => $label) {
            $count   = (int) ($counts[$mode]->total ?? 0);
            $rows[] = [
                'mode'       => $mode,
                'label'      => $label,
                'count'      => $count,
                'percentage' => $total > 0 ? round(($count / $total) * 100, 1) : 0,
            ];
        }

        return $rows;
    }
}
