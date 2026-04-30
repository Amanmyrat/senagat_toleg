<?php

namespace App\Http\Requests;

use App\Services\AlemTv\AlemTvTarifService;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class AlemTvCreateRequest extends FormRequest
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
            'type'    => ['required', Rule::in(['tv', 'iptv'])],
            'subject' => ['required', function ($attribute, $value, $fail) {
                if (is_int($value) || ctype_digit((string) $value)) {
                    return;
                }
                if (is_string($value) && str_starts_with($value, 'dalem')) {
                    return;
                }
                $fail("the_{$attribute}_must_be_an_integer_or_a_string_starting_with_dalem");
            }],
            'tarif'  => ['required', 'string', function ($attribute, $value, $fail) {
                $type   = $this->input('type');
                $tarifs = app(AlemTvTarifService::class)->getTarifs($type);
                $valid  = collect($tarifs)->pluck('tarif')->all();

                if (! in_array($value, $valid, true)) {
                    $fail("the_{$attribute}_{$value}_is_not_a_valid_tarif_for_type_{$type}");
                }
            }],
            'period' => ['required', 'integer', 'min:1', function ($attribute, $value, $fail) {
                $type   = $this->input('type');
                $tarif  = $this->input('tarif');
                $tarifs = app(AlemTvTarifService::class)->getTarifs($type);
                $found  = collect($tarifs)->firstWhere('tarif', $tarif);

                if ($found && $value > (int) $found['max_period']) {
                    $fail("the_{$attribute}_cannot_exceed_max_period_of_{$found['max_period']}_for_tarif_{$tarif}");
                }
            }],
        ];
    }
}
