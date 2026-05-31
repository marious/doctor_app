<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Carbon\Carbon;
use Illuminate\Http\JsonResponse;
use Modules\Advertisements\Models\Advertisement;
use Modules\Advertisements\Resources\AdvertisementResource;
use Modules\Appointments\Models\Appointment;
use Modules\Appointments\Resources\AppointmentResource;
use Modules\Articles\Models\Article;
use Modules\Articles\Resources\ArticleResource;
use Modules\Videos\Models\Video;
use Modules\Videos\Resources\VideoResource;

class HomeController extends Controller
{
    public function index(): JsonResponse
    {
        $tracking = auth()->user()->patientTracking;
        $audience = $this->resolvePatientAudience($tracking);

        $articles = Article::byNewest()
            ->when($audience, fn($q) => $q->forAudience($audience))
            ->get();

        $videos = Video::byNewest()
            ->when($audience, fn($q) => $q->forAudience($audience))
            ->get();

        
        $appointment = Appointment::with(['doctor', 'clinic'])
            ->where('patient_id', auth()->id())
            ->upcoming()
            ->orderBy('appointment_date')
            ->first();

        $ads = Advertisement::byNewest()->get();    
        
        return response()->json([
            'success' => true,
            'data'    => [
                'tracking_type' => $tracking?->tracking_type,
                'tracking'      => $this->resolveTracking($tracking),
                'articles'      => ArticleResource::collection($articles),
                'videos'        => VideoResource::collection($videos),
                'appointment'   => AppointmentResource::make($appointment),
                'ads'           => AdvertisementResource::collection($ads),
            ],
        ]);
    }

    private function resolveTracking($tracking): ?array
    {
        if (!$tracking) {
            return null;
        }

        if ($tracking->tracking_type === 'pregnancy') {
            $weeks     = $tracking->gestationalWeeks();
            $trimester = $tracking->trimester();
            $dueDate   = $tracking->estimated_due_date;

            return [
                'weekly_growth' => [
                    'current_week'     => $weeks,
                    'total_weeks'      => $tracking->totalWeeks(),
                    'headline'         => "You are {$weeks} weeks pregnant",
                    'progress_percent' => $tracking->progressPercent(),
                    'progress_label'   => $tracking->progressPercent() . '% Complete',
                    'progress_detail'  => "Week {$weeks}/{$tracking->totalWeeks()}",

                    'estimated_due_date' => [
                        'label' => 'Estimated Due Date',
                        'value' => $dueDate?->format('M d, Y'),
                        'raw'   => $dueDate?->toDateString(),
                    ],

                    'trimester' => [
                        'label'  => 'Current Trimester',
                        'value'  => $trimester['label'] ?? null,
                        'number' => $trimester['number'] ?? null,
                    ],

                    'days_remaining' => $tracking->daysRemaining(),
                ],
            ];
        }

        if ($tracking->tracking_type === 'menstrual') {
            $today      = Carbon::today();
            $nextPeriod = $tracking->next_period_date;
            $cycleDay   = $tracking->currentCycleDay();
            $daysUntil  = $nextPeriod ? max(0, (int) $today->diffInDays($nextPeriod, false)) : null;

            return [
                'calendar' => $tracking->menstrualCalendarData(
                    (int) $today->format('Y'),
                    (int) $today->format('n')
                ),

                'cycle_info' => [
                    'cycle_day' => $cycleDay,
                    'label'     => $cycleDay ? "Cycle Day {$cycleDay}" : null,
                    'message'   => $daysUntil !== null
                        ? ($daysUntil === 0
                            ? 'Your period is expected today.'
                            : "Your period is expected in {$daysUntil} " . ($daysUntil === 1 ? 'day.' : 'days.'))
                        : null,
                ],

                'current_phase' => $tracking->currentPhase(),

                'cycle_summary' => [
                    'cycle_day'                   => $cycleDay,
                    'cycle_length'                => $tracking->cycle_length,
                    'period_duration'             => $tracking->period_duration,
                    'last_menstruation_date'      => $tracking->last_menstruation_date?->toDateString(),
                    'last_menstruation_formatted' => $tracking->last_menstruation_date?->format('M d, Y'),
                    'next_period_date'            => $nextPeriod?->toDateString(),
                    'next_period_formatted'       => $nextPeriod?->format('M d, Y'),
                    'days_until_next_period'      => $daysUntil,
                    'ovulation_date'              => $tracking->ovulation_date?->toDateString(),
                    'ovulation_formatted'         => $tracking->ovulation_date?->format('M d, Y'),
                    'days_until_ovulation'        => $tracking->ovulation_date
                        ? max(0, (int) $today->diffInDays($tracking->ovulation_date, false))
                        : null,
                ],

                'statistical_report' => [
                    'regularity' => [
                        'label' => 'Regularity',
                        'value' => $tracking->cycleRegularity(),
                        'unit'  => '%',
                    ],
                    'duration' => [
                        'label' => 'Duration',
                        'value' => $tracking->averageCycleDuration(),
                        'unit'  => 'days',
                    ],
                    'symptom_frequency' => $tracking->symptomFrequency(),
                    'ai_summary'        => $tracking->menstrualAiSummary(),
                ],
            ];
        }

        return null;
    }

    private function resolvePatientAudience($tracking): ?string
    {
        if (!$tracking) {
            return null;
        }

        if ($tracking->tracking_type === 'pregnancy') {
            $trimester = $tracking->trimester();

            return match($trimester['number'] ?? null) {
                1       => 'pregnancy_1st',
                2       => 'pregnancy_2nd',
                3       => 'pregnancy_3rd',
                default => 'pregnancy',
            };
        }

        if ($tracking->tracking_type === 'menstrual') {
            return 'gynecology';
        }

        return null;
    }
}
