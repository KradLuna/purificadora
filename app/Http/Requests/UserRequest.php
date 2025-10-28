<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class UserRequest extends FormRequest
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
            'full_name' => [$flexRule, 'string', 'max:255', 'min:5'],
            'phone_number' => [$flexRule, Rule::unique('users')->ignore($this->user ?? 0)->whereNull('deleted_at')],
            'password' => [$flexRule, 'confirmed', 'min:4'],
            'is_active' => [$flexRule, 'boolean']
        ];
    }
}
