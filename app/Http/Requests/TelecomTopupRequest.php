<?php

namespace App\Http\Requests;

use App\Enum\ErrorMessage;
use Illuminate\Foundation\Http\FormRequest;

class TelecomTopupRequest extends FormRequest
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
             * Bank name .
             *
             * @var int
             *
             * @example rysgal
             */
            'bank_name' => ['required', 'string'],


            /**
             * Phone number.
             *
             * @var string
             *
             * @example 12932702
             */
            'phone' => ['required', 'string'],
            /**
             * Amount in manats.
             *
             * @var int
             *
             * @example 35
             */
            'amount' => ['required', 'numeric', 'min:1'],

        ];
    }

    public function messages(): array
    {
        return [

            'bank_name.required' => ErrorMessage::BANK_NAME_REQUIRED->value,
            'amount.required' => ErrorMessage::AMOUNT_REQUIRED->value,
            'amount.numeric' => ErrorMessage::AMOUNT_INVALID->value,
            'amount.min' => ErrorMessage::AMOUNT_MIN->value,
            'phone.required' => ErrorMessage::PHONE_NUMBER_REQUIRED->value,
            'phone.regex' => ErrorMessage::PHONE_NUMBER_INVALID->value,

        ];
    }
}
