<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class UpdateCartItemRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user('sanctum') !== null;
    }

    public function rules(): array
    {
        return [
            'quantity' => ['required', 'integer', 'min:1', 'max:100'],
        ];
    }

    public function messages(): array
    {
        return [
            'quantity.required' => 'Please specify a quantity.',
            'quantity.min' => 'Quantity must be at least 1.',
            'quantity.max' => 'Quantity must not exceed 100.',
        ];
    }
}
