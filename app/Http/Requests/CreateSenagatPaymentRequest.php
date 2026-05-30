<?php

namespace App\Http\Requests;

use App\Enum\SenagatPaymentTypeEnum;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class CreateSenagatPaymentRequest extends FormRequest
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
             * Amount in cent.
             *
             * @var int
             *
             * @example 3500
             */
            'amount' => ['required', 'integer', 'min:1'],

           /**
            * Amount in cent.
            *
            * @var string
            *
            * @example certificate
            */
            'type' => ['required', 'string',Rule::in(SenagatPaymentTypeEnum::values())],

        ];
    }
}
