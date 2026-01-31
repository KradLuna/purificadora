<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class RecordRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     *
     * @return bool
     */
    public function authorize()
    {
        return true;
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, mixed>
     */
    public function rules()
    {
        $flexRule = $this->getMethod() == 'POST' ? 'required' : 'nullable';
        return [
            'record_type_id' => [$flexRule, Rule::exists('record_types', 'id')],
            'value' => [
                'nullable',
                'numeric',
                'min:0',
                'max:9999',
                'regex:/^\d+(\.\d{1,2})?$/', // máximo 2 decimales
                Rule::requiredIf(
                    fn() =>
                    $this->getMethod() === 'POST'
                        && (int) $this->record_type_id !== 3
                ),
            ],
            'evidence' => [
                'nullable',
                'file',
                'mimes:jpg,png,pdf',
                'max:2048',
                Rule::requiredIf(function () {
                    return $this->getMethod() == 'POST'
                        && (int) $this->record_type_id !== 2; // id corte de caja
                })
            ],
            'record_date' => ['sometimes', 'date_format:Y-m-d\TH:i']
        ];
    }

    public function messages(): array
    {
        return [
            'value.numeric'  => 'El valor debe ser un número.',
            'value.min'      => 'El valor no puede ser negativo.',
            'value.max'      => 'El valor máximo permitido es 9999',
            'value.regex'    => 'El valor solo puede tener hasta 2 decimales.',
        ];
    }
}
