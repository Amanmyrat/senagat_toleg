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
            /**
             * Command
             *
             * @var string
             *
             * @example 1
             */
            'command'  => 'required|in:check,pay',

            'txn_id'   => 'required|string|max:20',

            'account'  => 'required|string|max:200',

            'amount'   => 'required|numeric|min:0.01',

            'txn_date' => 'nullable|string',
        ];
    }
}
