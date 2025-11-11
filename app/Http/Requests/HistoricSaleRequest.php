<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class HistoricSaleRequest extends FormRequest
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
        return [
            'product_id' => ['required', Rule::exists('products', 'id')->whereNull('deleted_at')],
            'employee_id' => ['required', Rule::exists('users', 'id')->whereNull('deleted_at')],
            'amount' => ['required', 'integer', 'min:1'],
            'created_at' => ['required', 'date', 'before_or_equal:now'],
            'total' => ['required', 'regex:/^\d+(\.\d{1,2})?$/'],
        ];
    }
}
