<?php

namespace App\Http\Requests\Telecom;

use Illuminate\Foundation\Http\FormRequest;

class TelecomPaymentRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        return true;
    }


    public function rules(): array
    {
        return [

            'account'  => 'required|string|max:200',

            'amount'   => 'required|numeric|min:0.01',

        ];
    }
}
