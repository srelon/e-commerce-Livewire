<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class CartItemRequest extends FormRequest
{
    public function authorize(): bool {
        return true;
    }

    public function rules(): array {
        return [
            'slug' => ['required', 'string', 'exists:products,slug'],
            'quantity' => ['required', 'integer', 'min:1'],
        ];
    }
}
