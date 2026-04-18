<?php

namespace Modules\Users\Resources;

use Carbon\Carbon;
use Illuminate\Http\Request;
use Modules\Core\Resources\MediaResource;
use Modules\Core\CustomResource;

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
        $nextPeriod = $this->next_period_date;
        $today      = Carbon::today();
 
        return [
            'tracking_id'    => $this->id,
            'tracking_type'  => 'menstrual',
            'mode_label'     => 'Period Mode',
 
            'calendar' => $this->calendarData(
                (int) $monthCarbon->format('Y'),
                (int) $monthCarbon->format('n')
            ),
 
            'cycle_summary' => [
                'last_menstruation_date' => $this->last_menstruation_date?->toDateString(),
                'cycle_length'           => $this->cycle_length,
                'period_duration'        => $this->period_duration,
                'next_period_date'       => $nextPeriod?->toDateString(),
                'next_period_formatted'  => $nextPeriod?->format('M d, Y'),
                'days_until_next_period' => $nextPeriod
                    ? max(0, (int) $today->diffInDays($nextPeriod, false))
                    : null,
                'ovulation_date'         => $this->ovulation_date?->toDateString(),
                'ovulation_formatted'    => $this->ovulation_date?->format('M d, Y'),
            ],
 
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
}