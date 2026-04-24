<?php

namespace App\Http\Requests\Api;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class StorePatientTrackingRequest extends FormRequest
{
    protected function prepareForValidation()
    {
        if ($this->input('tracking_type') === 'pregnancy' && is_null($this->input('pregnancy_test_status'))) {
            $this->merge([
                'pregnancy_test_status' => 'positive',
            ]);
        }
    }

    public function rules(): array
    {
        $type = $this->input('tracking_type');

        $rules = [
            'tracking_type' => ['required', Rule::in(['menstrual', 'pregnancy'])],
        ];


        if ($type === 'menstrual') {
            $rules = array_merge($rules, [
                'last_menstruation_date' => ['required', 'date', 'before_or_equal:today'],
                'cycle_length' => ['required', 'integer', 'min:20', 'max:45'],
                'period_duration' => ['required', 'integer', 'min:1', 'max:10'],
            ]);
        }


        if ($type === 'pregnancy') {
            $rules = array_merge($rules, [
                'lmp_date' => ['required', 'date', 'before_or_equal:today'],
                'pregnancy_test_status' => ['nullable', Rule::in(['positive', 'negative', 'not_sure'])],
            ]);
        }

        return $rules;
    }

    public function messages(): array
    {
        return [
            'tracking_type.required' => __('Please select a tracking type.'),
            'tracking_type.in' => __('Tracking type must be either menstrual or pregnancy.'),
            'last_menstruation_date.required' => __('Last menstruation date is required.'),
            'last_menstruation_date.before_or_equal' => __('Last menstruation date cannot be in the future.'),
            'cycle_length.required' => __('Cycle length is required.'),
            'cycle_length.min' => __('Cycle length must be at least 20 days.'),
            'cycle_length.max' => __('Cycle length cannot exceed 45 days.'),
            'period_duration.required' => __('Period duration is required.'),
            'period_duration.min' => __('Period duration must be at least 1 day.'),
            'period_duration.max' => __('Period duration cannot exceed 10 days.'),
            'lmp_date.required' => __('First day of LMP is required.'),
            'lmp_date.before_or_equal' => __('LMP date cannot be in the future.'),
            'pregnancy_test_status.required' => __('Pregnancy test status is required.'),
            'pregnancy_test_status.in' => __('Invalid pregnancy test status.'),
        ];
    }
}
