<?php

namespace App\Http\Requests\Telecom;

use Illuminate\Foundation\Http\FormRequest;

class TelecomBalanceRequest extends FormRequest
{
    public function rules(): array
    {
        return [
            'account' => 'required|string|max:200',
        ];
    }

    public function authorize(): bool
    {
        return true;
    }
}
