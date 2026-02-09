<?php

namespace App\Http\Requests;

use App\Enum\ErrorMessage;
use Illuminate\Foundation\Http\FormRequest;

class CheckPaymentStatusRequest extends FormRequest
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
             * Order Id.
             *
             * @var string
             *
             * @example 1
             */
            'orderId' => ['required', 'string', 'exists:payments,order_id'],
        ];
    }

    public function messages(): array
    {
        return [
            'orderId.required_without' => ErrorMessage::ORDER_ID_OR_PAY_ID_REQUIRED->value,
            'orderId.string' => ErrorMessage::ORDER_ID_INVALID->value,

        ];
    }
}
