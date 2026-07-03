<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class ProductFilterRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    protected function prepareForValidation(): void
    {
        foreach (['category', 'author', 'status'] as $key) {
            if (!$this->has($key) || is_array($this->input($key))) {
                continue;
            }

            $value = $this->input($key);
            $this->merge([$key => str_contains($value, ',') ? explode(',', $value) : [$value]]);
        }

        foreach (['price_min', 'price_max'] as $key) {
            if ($this->has($key) && !is_numeric($this->input($key))) {
                $this->query->remove($key);
                $this->request->remove($key);
            }
        }
    }

    public function rules(): array
    {
        return [
            'category' => ['sometimes', 'array'],
            'category.*' => ['string'],
            'author' => ['sometimes', 'array'],
            'author.*' => ['string'],
            'price_min' => ['sometimes', 'numeric', 'min:0'],
            'price_max' => ['sometimes', 'numeric', 'min:0'],
            'rating' => ['sometimes', 'integer', 'between:1,5'],
            'status' => ['sometimes', 'array'],
            'status.*' => ['string', Rule::in(['In Stock', 'On Sale'])],
            'sort_by' => ['sometimes', Rule::in(['default', 'popularity', 'rating', 'price_asc', 'price_desc'])],
        ];
    }

    public function filters(): array
    {
        return [
            'category' => $this->input('category'),
            'author' => $this->input('author'),
            'price_min' => $this->has('price_min') ? (float) $this->input('price_min') : null,
            'price_max' => $this->has('price_max') ? (float) $this->input('price_max') : null,
            'rating' => $this->has('rating') ? (int) $this->input('rating') : null,
            'status' => $this->input('status'),
            'sort_by' => $this->input('sort_by', 'default'),
        ];
    }
}
