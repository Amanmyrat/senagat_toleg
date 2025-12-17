<?php

namespace App\Http\Requests;

use App\Enum\ErrorMessage;
use Illuminate\Foundation\Http\FormRequest;

class BeletBalanceTopUpRequest extends FormRequest
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
             * Bank Id.
             *
             * @var int
             *
             * @example 1
             */
            'bank_id' => ['required', 'integer'],
            /**
             * Amount in manats.
             *
             * @var int
             *
             * @example 35
             */
            'amount_in_manats' => ['required', 'numeric', 'min:1'],

            /**
             * Phone number.
             *
             * @var string
             *
             * @example 99365021730
             */
            'phone' => ['required', 'string', 'regex:/^[0-9]{11}$/'],

            /**
             * Return Url.
             *
             *
             * @example 99365021730
             */
            'returnUrl' => ['required', 'url'],

        ];
    }

    public function messages(): array
    {
        return [
            'bank_id.required' => ErrorMessage::BANK_ID_REQUIRED->value,
            'bank_id.integer' => ErrorMessage::BANK_ID_INVALID->value,
            'amount_in_manats.required' => ErrorMessage::AMOUNT_REQUIRED->value,
            'amount_in_manats.numeric' => ErrorMessage::AMOUNT_INVALID->value,
            'amount_in_manats.min' => ErrorMessage::AMOUNT_MIN->value,
            'phone.required' => ErrorMessage::PHONE_NUMBER_REQUIRED->value,
            'phone.regex' => ErrorMessage::PHONE_NUMBER_INVALID->value,
            'returnUrl.required' => ErrorMessage::RETURN_URL_REQUIRED->value,
            'returnUrl.url' => ErrorMessage::RETURN_URL_INVALID->value,
        ];
    }
}
