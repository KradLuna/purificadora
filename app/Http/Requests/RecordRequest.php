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
            'value' => [$flexRule, 'regex:/^\d+(\.\d{1,2})?$/'],
            'evidence' => [
                'nullable',
                'file',
                'mimes:jpg,png,pdf',
                'max:2048',
                Rule::requiredIf(function () {
                    return $this->getMethod() == 'POST'
                        && (int) $this->record_type_id !== 2; // id específico
                })
            ],
            'record_date' => ['sometimes', 'date_format:Y-m-d\TH:i']
        ];
    }
}
