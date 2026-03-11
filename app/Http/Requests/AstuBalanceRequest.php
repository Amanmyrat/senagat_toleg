<?php

namespace App\Http\Requests;

use App\Enum\TopupTypeEnum;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class AstuBalanceRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'account' => 'required|string|max:50',
            'type' => ['required',  Rule::in(TopupTypeEnum::values())],
        ];
    }
}
