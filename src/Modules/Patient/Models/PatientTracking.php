<?php

namespace Modules\Patient\Models;

use Carbon\Carbon;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasOne;
use Modules\Users\Models\User;

class PatientTracking extends Model
{
    protected $fillable = [
        'patient_id',
        'tracking_type',

        // Menstrual
        'last_menstruation_date',
        'cycle_length',
        'period_duration',

        // Pregnancy
        'lmp_date',
        'pregnancy_test_status',

        // Computed
        'next_period_date',
        'ovulation_date',
        'estimated_due_date',
    ];

    protected $casts = [
        'last_menstruation_date' => 'date',
        'lmp_date' => 'date',
        'next_period_date' => 'date',
        'ovulation_date' => 'date',
        'estimated_due_date' => 'date',
        'cycle_length' => 'integer',
        'period_duration' => 'integer',
    ];

    // ─── Relationships ────────────────────────────────────────────────────────
    public function patient(): BelongsTo
    {
        return $this->belongsTo(User::class, 'patient_id');
    }

    public function healthStats(): HasMany
    {
        return $this->hasMany(HealthStat::class);
    }
 
    public function latestHealthStat(): HasOne
    {
        return $this->hasOne(HealthStat::class, 'patient_tracking_id')->latestOfMany('recorded_at');
    }

    // ─── Computed Helpers ─────────────────────────────────────────────────────


    /**
     * Calculate next period date based on last menstruation + cycle length.
     */
    public function computeNextPeriodDate(): ?Carbon
    {
        if (!$this->last_menstruation_date || !$this->cycle_length) {
            return null;
        }

        return $this->last_menstruation_date->copy()->addDays($this->cycle_length);
    }

    /**
     * Ovulation typically occurs ~14 days before next period.
     */
    public function computeOvulationDate(): ?Carbon
    {
        $nextPeriod = $this->computeNextPeriodDate();

        return $nextPeriod?->copy()->subDays(14);
    }

    /**
     * Estimated due date = LMP + 280 days (Naegele's Rule).
     */
    public function computeEstimatedDueDate(): ?Carbon
    {
        if (!$this->lmp_date) {
            return null;
        }

        return $this->lmp_date->copy()->addDays(280);
    }

    /**
     * Gestational age in weeks based on LMP.
     */
    public function gestationalAgeInWeeks(): ?int
    {
        if (!$this->lmp_date) {
            return null;
        }

        return (int) $this->lmp_date->diffInWeeks(Carbon::today());
    }

    // ─── Pregnancy Computed ───────────────────────────────────────────────────
 
    /**
     * Gestational age in complete weeks from LMP to today
     */
    public function gestationalWeeks(): ?int
    {
        if (!$this->lmp_date) return null;
        return (int) $this->lmp_date->diffInWeeks(Carbon::today());
    }
 
    /**
     * Total pregnancy weeks (standard = 40)
     */
    public function totalWeeks(): int
    {
        return 40;
    }

        /**
     * Pregnancy progress as percentage (0–100)
     */
    public function progressPercent(): ?int
    {
        $weeks = $this->gestationalWeeks();
        if ($weeks === null) return null;
        return (int) min(100, round(($weeks / $this->totalWeeks()) * 100));
    }
 
    /**
     * Current trimester label and number
     */
    public function trimester(): ?array
    {
        $weeks = $this->gestationalWeeks();
        if ($weeks === null) return null;
 
        if ($weeks <= 12) {
            return ['number' => 1, 'label' => '1st Trimester'];
        } elseif ($weeks <= 27) {
            return ['number' => 2, 'label' => '2nd Trimester'];
        } else {
            return ['number' => 3, 'label' => '3rd Trimester'];
        }
    }
 
    /**
     * Days remaining until estimated due date
     */
    public function daysRemaining(): ?int
    {
        if (!$this->estimated_due_date) return null;
        return max(0, (int) Carbon::today()->diffInDays($this->estimated_due_date, false));
    }
 
    /**
     * Baby size description by week (for Weekly Growth Update card)
     */
    public function babySizeByWeek(): ?array
    {
        $weeks = $this->gestationalWeeks();
        if ($weeks === null) return null;
 
        $sizes = [
            4  => ['size' => 'Poppy seed',    'length_cm' => 0.1,  'weight_g' => null],
            5  => ['size' => 'Sesame seed',    'length_cm' => 0.2,  'weight_g' => null],
            6  => ['size' => 'Sweet pea',      'length_cm' => 0.6,  'weight_g' => null],
            7  => ['size' => 'Blueberry',      'length_cm' => 1.3,  'weight_g' => 1],
            8  => ['size' => 'Kidney bean',    'length_cm' => 1.6,  'weight_g' => 1],
            9  => ['size' => 'Grape',          'length_cm' => 2.3,  'weight_g' => 2],
            10 => ['size' => 'Strawberry',     'length_cm' => 3.1,  'weight_g' => 4],
            11 => ['size' => 'Lime',           'length_cm' => 4.1,  'weight_g' => 7],
            12 => ['size' => 'Plum',           'length_cm' => 5.4,  'weight_g' => 14],
            13 => ['size' => 'Peach',          'length_cm' => 7.4,  'weight_g' => 23],
            14 => ['size' => 'Lemon',          'length_cm' => 8.7,  'weight_g' => 43],
            15 => ['size' => 'Apple',          'length_cm' => 10.1, 'weight_g' => 70],
            16 => ['size' => 'Avocado',        'length_cm' => 11.6, 'weight_g' => 100],
            17 => ['size' => 'Pear',           'length_cm' => 13.0, 'weight_g' => 140],
            18 => ['size' => 'Sweet potato',   'length_cm' => 14.2, 'weight_g' => 190],
            19 => ['size' => 'Mango',          'length_cm' => 15.3, 'weight_g' => 240],
            20 => ['size' => 'Banana',         'length_cm' => 16.4, 'weight_g' => 300],
            21 => ['size' => 'Carrot',         'length_cm' => 26.7, 'weight_g' => 360],
            22 => ['size' => 'Papaya',         'length_cm' => 27.8, 'weight_g' => 430],
            23 => ['size' => 'Grapefruit',     'length_cm' => 28.9, 'weight_g' => 501],
            24 => ['size' => 'Corn',           'length_cm' => 30.0, 'weight_g' => 600],
            25 => ['size' => 'Cauliflower',    'length_cm' => 34.6, 'weight_g' => 660],
            26 => ['size' => 'Scallion',       'length_cm' => 35.6, 'weight_g' => 760],
            27 => ['size' => 'Rutabaga',       'length_cm' => 36.6, 'weight_g' => 875],
            28 => ['size' => 'Eggplant',       'length_cm' => 37.6, 'weight_g' => 1005],
            30 => ['size' => 'Cabbage',        'length_cm' => 39.9, 'weight_g' => 1319],
            32 => ['size' => 'Squash',         'length_cm' => 42.4, 'weight_g' => 1702],
            34 => ['size' => 'Cantaloupe',     'length_cm' => 45.0, 'weight_g' => 2146],
            36 => ['size' => 'Romaine lettuce','length_cm' => 47.4, 'weight_g' => 2622],
            38 => ['size' => 'Leek',           'length_cm' => 49.8, 'weight_g' => 3083],
            40 => ['size' => 'Small pumpkin',  'length_cm' => 51.2, 'weight_g' => 3462],
        ];
 
        // Find closest week entry
        $entry = null;
        foreach ($sizes as $w => $data) {
            if ($weeks >= $w) $entry = $data;
        }
 
        return $entry ? array_merge($entry, ['week' => $weeks]) : null;
    }
 
    /**
     * Build calendar data for a given month
     * Returns days with type: current_week | past | upcoming
     */
    public function calendarData(int $year, int $month): array
    {
        if (!$this->lmp_date) return [];
 
        $today          = Carbon::today();
        $currentWeekStart = $today->copy()->startOfWeek(Carbon::SUNDAY);
        $currentWeekEnd   = $today->copy()->endOfWeek(Carbon::SATURDAY);
 
        $startOfMonth = Carbon::create($year, $month, 1);
        $endOfMonth   = $startOfMonth->copy()->endOfMonth();
 
        $days = [];
        $cursor = $startOfMonth->copy();
 
        while ($cursor->lte($endOfMonth)) {
            $date = $cursor->copy();
 
            if ($date->between($currentWeekStart, $currentWeekEnd)) {
                $type = 'current_week';
            } elseif ($date->lt($currentWeekStart)) {
                $type = 'past';
            } else {
                $type = 'upcoming';
            }
 
            $days[] = [
                'date'      => $date->toDateString(),
                'day'       => (int) $date->format('j'),
                'day_of_week' => $date->format('D')[0], // S M T W T F S
                'type'      => $type,
                'is_today'  => $date->isToday(),
            ];
 
            $cursor->addDay();
        }
 
        return [
            'year'        => $year,
            'month'       => $month,
            'month_name'  => $startOfMonth->format('F Y'),
            'trimester'   => $this->trimester()['label'] ?? null,
            'days'        => $days,
            'legend'      => [
                ['type' => 'current_week', 'label' => 'Current Week'],
                ['type' => 'past',         'label' => 'Past Weeks'],
                ['type' => 'upcoming',     'label' => 'Upcoming'],
            ],
        ];
    }
 
    /**
     * AI health tip based on current week
     */
    public function aiHealthTip(): ?array
    {
        $weeks = $this->gestationalWeeks();
        if ($weeks === null) return null;
 
        $tips = [
            // First Trimester
            ['min' => 1,  'max' => 4,  'tip' => __('Start taking folic acid 400mcg daily. Avoid alcohol and smoking. Schedule your first prenatal visit.')],
            ['min' => 5,  'max' => 8,  'tip' => __('Morning sickness is common now. Try small frequent meals, ginger tea, and stay hydrated. Rest as much as possible.')],
            ['min' => 9,  'max' => 12, 'tip' => __('Your baby\'s organs are forming. Eat folate-rich foods like leafy greens, beans, and citrus fruits.')],
            ['min' => 13, 'max' => 16, 'tip' => __('Second trimester begins! Energy usually improves. Focus on iron-rich foods to support increased blood volume.')],
            // Second Trimester
            ['min' => 17, 'max' => 20, 'tip' => __('Time for your anatomy scan. Calcium and Vitamin D are crucial for baby\'s bone development.')],
            ['min' => 21, 'max' => 24, 'tip' => __('Your weight gain is within the optimal range for the 24th week. Keep focusing on iron-rich foods and light exercise.')],
            ['min' => 25, 'max' => 27, 'tip' => __('Baby\'s hearing is developing. Talk and play music. Monitor for signs of gestational diabetes.')],
            // Third Trimester
            ['min' => 28, 'max' => 32, 'tip' => __('Begin monitoring baby\'s kick counts daily. Sleep on your left side to improve circulation.')],
            ['min' => 33, 'max' => 36, 'tip' => __('Pack your hospital bag. Practice breathing exercises. Attend all prenatal check-ups.')],
            ['min' => 37, 'max' => 40, 'tip' => __('Baby is full term! Watch for signs of labor. Stay in close contact with your healthcare provider.')],
        ];
 
        foreach ($tips as $tip) {
            if ($weeks >= $tip['min'] && $weeks <= $tip['max']) {
                return [
                    'week'    => $weeks,
                    'message' => $tip['tip'],
                    'type'    => 'ai_health_assistant',
                ];
            }
        }
 
        return ['week' => $weeks, 'message' => __('Keep attending your prenatal check-ups regularly.'), 'type' => 'ai_health_assistant'];
    }
}