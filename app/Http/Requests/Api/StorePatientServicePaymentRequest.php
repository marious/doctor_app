<?php

namespace App\Http\Requests\Api;

use Illuminate\Foundation\Http\FormRequest;

class StorePatientServicePaymentRequest extends FormRequest
{
    public function rules(): array
    {
        return [
            'amount'       => ['required', 'numeric', 'min:0.01'],
            'payment_date' => ['required', 'date'],
        ];
    }
}
