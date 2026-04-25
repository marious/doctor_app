<?php

namespace Modules\Users\Resources;

use Carbon\Carbon;
use Illuminate\Http\Request;
use Modules\Core\CustomResource;
use Modules\Patient\Models\CycleSymptomLog;
use Modules\Patient\Models\Symptom;

class TrackerResource extends CustomResource
{

    public function data(Request $request): array
    {
        // Calendar month — default to current month, allow ?month=2026-02 override
        $monthParam  = $request->query('month');
        $monthCarbon = $monthParam
            ? Carbon::createFromFormat('Y-m', $monthParam)
            : Carbon::today();
 
        $latestStat = $this->latestHealthStat;
 
        // ── Mode: pregnancy ───────────────────────────────────────────────────
        if ($this->tracking_type === 'pregnancy') {
            $weeks     = $this->gestationalWeeks();
            $trimester = $this->trimester();
            $dueDate   = $this->estimated_due_date;
            $babySize  = $this->babySizeByWeek();
            $aiTip     = $this->aiHealthTip();
 
            return [
                'tracking_id'    => $this->id,
                'tracking_type'  => 'pregnancy',
                'mode_label'     => 'Pregnancy Mode',
 
                // ── Calendar section ──────────────────────────────────────────
                'calendar' => $this->calendarData(
                    (int) $monthCarbon->format('Y'),
                    (int) $monthCarbon->format('n')
                ),
 
                // ── Weekly Growth Update card ─────────────────────────────────
                'weekly_growth' => [
                    'current_week'     => $weeks,
                    'total_weeks'      => $this->totalWeeks(),
                    'headline'         => "You are {$weeks} weeks pregnant",
                    'progress_percent' => $this->progressPercent(),
                    'progress_label'   => $this->progressPercent() . '% Complete',
                    'progress_detail'  => "Week {$weeks}/{$this->totalWeeks()}",
 
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
 
                    'days_remaining' => $this->daysRemaining(),
                ],
 
                // ── Baby size (bonus data for richer UI) ──────────────────────
                'baby_size' => $babySize ? [
                    'week'      => $babySize['week'],
                    'size_name' => $babySize['size'],
                    'length_cm' => $babySize['length_cm'],
                    'weight_g'  => $babySize['weight_g'],
                ] : null,
 
                // ── Health Statistics section ─────────────────────────────────
                'health_stats' => [
                    'weight' => [
                        'label'  => 'Current Weight',
                        'value'  => $latestStat?->weight_kg,
                        'unit'   => 'kg',
                        'status' => $latestStat?->weight_status,
                        'status_label' => $latestStat
                            ? match($latestStat->weight_status) {
                                'healthy_gain' => 'Healthy Gain',
                                'underweight'  => 'Below Target',
                                'overweight'   => 'Above Target',
                                default        => null,
                            }
                            : null,
                    ],
                    'bpm' => [
                        'label' => 'BPM Avg',
                        'value' => $latestStat?->bpm,
                        'unit'  => 'Beats / min',
                    ],
                    'recorded_at' => $latestStat?->recorded_at?->toDateTimeString(),
                ],
 
                // ── AI Health Assistant tip ───────────────────────────────────
                'ai_health_tip' => $aiTip,
 
                // ── Meta ──────────────────────────────────────────────────────
                'lmp_date'             => $this->lmp_date?->toDateString(),
                'pregnancy_test_status'=> $this->pregnancy_test_status,
                'created_at'           => $this->created_at?->toDateTimeString(),
            ];
        }
 
        // ── Mode: menstrual ───────────────────────────────────────────────────
        $nextPeriod   = $this->next_period_date;
        $today        = Carbon::today();
        $currentPhase = $this->currentPhase();
        $cycleDay     = $this->currentCycleDay();
        $daysUntil    = $nextPeriod ? max(0, (int) $today->diffInDays($nextPeriod, false)) : null;

        return [
            'tracking_id'   => $this->id,
            'tracking_type' => 'menstrual',
            'mode_label'    => 'Period Mode',

            // ── Calendar ─────────────────────────────────────────────────────
            'calendar' => $this->menstrualCalendarData(
                (int) $monthCarbon->format('Y'),
                (int) $monthCarbon->format('n')
            ),

            // ── Cycle info notification bar ───────────────────────────────────
            'cycle_info' => [
                'cycle_day' => $cycleDay,
                'label'     => $cycleDay ? "Cycle Day {$cycleDay}" : null,
                'message'   => $daysUntil !== null
                    ? ($daysUntil === 0
                        ? 'Your period is expected today.'
                        : "Your period is expected in {$daysUntil} " . ($daysUntil === 1 ? 'day.' : 'days.'))
                    : null,
            ],

            // ── Current phase ─────────────────────────────────────────────────
            'current_phase' => $currentPhase,

            // ── Cycle summary ─────────────────────────────────────────────────
            'cycle_summary' => [
                'cycle_day'                   => $cycleDay,
                'cycle_length'                => $this->cycle_length,
                'period_duration'             => $this->period_duration,
                'last_menstruation_date'      => $this->last_menstruation_date?->toDateString(),
                'last_menstruation_formatted' => $this->last_menstruation_date?->format('M d, Y'),
                'next_period_date'            => $nextPeriod?->toDateString(),
                'next_period_formatted'       => $nextPeriod?->format('M d, Y'),
                'days_until_next_period'      => $daysUntil,
                'ovulation_date'              => $this->ovulation_date?->toDateString(),
                'ovulation_formatted'         => $this->ovulation_date?->format('M d, Y'),
                'days_until_ovulation'        => $this->ovulation_date
                    ? max(0, (int) $today->diffInDays($this->ovulation_date, false))
                    : null,
            ],

            // ── Statistical report ────────────────────────────────────────────
            'statistical_report' => [
                'regularity' => [
                    'label' => 'Regularity',
                    'value' => $this->cycleRegularity(),
                    'unit'  => '%',
                ],
                'duration' => [
                    'label' => 'Duration',
                    'value' => $this->averageCycleDuration(),
                    'unit'  => 'days',
                ],
                'symptom_frequency' => $this->symptomFrequency(),
                'ai_summary'        => $this->menstrualAiSummary(),
            ],

            // ── Symptom picker (all options + today's selection) ──────────────
            'symptom_picker' => $this->buildSymptomPicker($today),

            // ── Health stats ──────────────────────────────────────────────────
            'health_stats' => [
                'weight' => [
                    'label' => 'Current Weight',
                    'value' => $latestStat?->weight_kg,
                    'unit'  => 'kg',
                ],
                'bpm' => [
                    'label' => 'BPM Avg',
                    'value' => $latestStat?->bpm,
                    'unit'  => 'Beats / min',
                ],
                'recorded_at' => $latestStat?->recorded_at?->toDateTimeString(),
            ],

            'created_at' => $this->created_at?->toDateTimeString(),
        ];
    }

    private function buildSymptomPicker(Carbon $today): array
    {
        // Today's logged symptoms for this tracking record
        $todayLogs = CycleSymptomLog::where('patient_tracking_id', $this->id)
            ->where('logged_date', $today->toDateString())
            ->with('symptom')
            ->get()
            ->keyBy('symptom_id');

        // All symptoms grouped
        $groups = Symptom::orderBy('sort_order')
            ->get()
            ->groupBy('group')
            ->map(fn($items, $group) => [
                'group'    => $group,
                'label'    => match($group) {
                    'general'           => 'General Symptoms',
                    'mood'              => 'Mood',
                    'vaginal_discharge' => 'Vaginal Discharge',
                    default             => ucfirst($group),
                },
                'symptoms' => $items->map(fn($s) => [
                    'id'          => $s->id,
                    'key'         => $s->key,
                    'label'       => $s->label,
                    'is_selected' => $todayLogs->has($s->id),
                    'severity'    => $todayLogs->get($s->id)?->severity,
                ])->values(),
            ])
            ->values();

        return [
            'logged_date' => $today->toDateString(),
            'groups'      => $groups,
            'severities'  => [
                ['key' => 'mild',     'label' => 'Mild'],
                ['key' => 'moderate', 'label' => 'Moderate'],
                ['key' => 'severe',   'label' => 'Severe'],
            ],
        ];
    }
}