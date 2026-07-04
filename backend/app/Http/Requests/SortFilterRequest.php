<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

abstract class SortFilterRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'sort_by' => ['sometimes', Rule::in($this->sortKeys())],
        ];
    }

    public function filters(): array
    {
        return [
            'sort_by' => $this->input('sort_by', $this->sortKeys()[0]),
        ];
    }

    abstract protected function sortKeys(): array;
}
