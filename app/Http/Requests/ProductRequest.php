<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class ProductRequest extends FormRequest
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
            'name' => [$flexRule, 'max:50', Rule::unique('products')->ignore($this->product ?? 0)->whereNull('deleted_at')],
            'price' => [$flexRule, 'regex:/^\d+(\.\d{1,2})?$/'],
            'is_active' => [$flexRule, 'boolean'],
            'liters' => [$flexRule, 'regex:/^\d+(\.\d{1,2})?$/'],
            'stock' => [$flexRule, 'numeric', 'min:0', 'max:100']
        ];
    }
}
