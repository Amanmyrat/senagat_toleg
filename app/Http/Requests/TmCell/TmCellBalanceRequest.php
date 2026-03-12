<?php

namespace App\Http\Requests\TmCell;

use App\Enum\ErrorMessage;
use Illuminate\Foundation\Http\FormRequest;

class TmCellBalanceRequest extends FormRequest
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
             * Phone number.
             *
             * @var string
             *
             * @example 65021730
             */
            'phone' => ['required', 'string', 'regex:/^[0-9]{8}$/'],
        ];
    }
    public function messages(): array
    {
        return [
            'phone.required' => ErrorMessage::PHONE_NUMBER_REQUIRED->value,
            'phone.regex' => ErrorMessage::PHONE_NUMBER_INVALID->value,
        ];
    }
}
