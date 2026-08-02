<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class CartSyncRequest extends FormRequest
{
    public function authorize(): bool {
        return true;
    }

    public function rules(): array {
        return [
            'items' => ['required', 'array'],
            'items.*.slug' => ['required', 'string', 'exists:products,slug'],
            'items.*.quantity' => ['required', 'integer', 'min:1'],
        ];
    }
}
