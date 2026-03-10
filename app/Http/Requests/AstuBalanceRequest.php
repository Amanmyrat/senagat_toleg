<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

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
            'type'    => 'required|string|in:phone,iptv,inet',
        ];
    }
}
