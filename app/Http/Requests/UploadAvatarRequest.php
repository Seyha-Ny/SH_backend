<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class UploadAvatarRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user('sanctum') !== null;
    }

    public function rules(): array
    {
        return [
            'avatar' => ['nullable', 'string', 'max:8000000'],
        ];
    }

    public function messages(): array
    {
        return [
            'avatar.max' => 'Avatar image must not exceed 8MB.',
        ];
    }
}
