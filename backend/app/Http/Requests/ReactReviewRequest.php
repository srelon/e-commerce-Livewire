<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class ReactReviewRequest extends FormRequest
{
    public function authorize(): bool {
        return true;
    }

    public function rules(): array {
        return [
            'type' => ['required', 'in:like,dislike'],
        ];
    }
}
