<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class OrderActionRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user('sanctum') !== null;
    }

    public function rules(): array
    {
        return [
            'reason' => ['nullable', 'string', 'max:500'],
        ];
    }

    public function messages(): array
    {
        return [
            'reason.max' => 'Reason must not exceed 500 characters.',
        ];
    }
}
