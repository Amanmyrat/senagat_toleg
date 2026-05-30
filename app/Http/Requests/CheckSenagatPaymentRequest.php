<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class CheckSenagatPaymentRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        return true;
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, \Illuminate\Contracts\Validation\ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        return [
            /**
             * Bank Id
             *
             * @var string
             *
             * @example 1
             */
            'location_id' => ['required', 'string', 'exists:merchants,location_id'],

            /**
             * Order Id.
             *
             * @var string
             *
             * @example 1
             */
            'orderId' => ['required', 'string', 'exists:payments,order_id'],
            ];
    }
}
