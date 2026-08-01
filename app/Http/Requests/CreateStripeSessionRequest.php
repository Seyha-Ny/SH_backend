<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class CreateStripeSessionRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user('sanctum') !== null;
    }

    public function rules(): array
    {
        return [
            'address' => ['required', 'string', 'max:255'],
            'city' => ['required', 'string', 'max:255'],
            'postal_code' => ['required', 'string', 'max:20'],
            'phone' => ['required', 'string', 'max:20', 'regex:/^[+\d\s\-\(\)]+$/'],
            'shipping_method_id' => ['nullable', 'integer', 'exists:courier_shipping_methods,id'],
            'coupon_code' => ['nullable', 'string', 'max:50'],
        ];
    }

    public function messages(): array
    {
        return [
            'address.required' => 'Please enter your shipping address.',
            'city.required' => 'Please enter your city.',
            'postal_code.required' => 'Please enter your postal code.',
            'phone.required' => 'Please enter your phone number.',
            'phone.regex' => 'Please enter a valid phone number.',
            'shipping_method_id.exists' => 'The selected shipping method is invalid.',
        ];
    }
}
