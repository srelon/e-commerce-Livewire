<?php

namespace App\Http\Requests;

class NewsFilterRequest extends SortFilterRequest
{
    protected function sortKeys(): array {
        return ['newest', 'oldest'];
    }

    public function rules(): array {
        return array_merge(parent::rules(), [
            'category' => ['sometimes', 'string'],
        ]);
    }

    public function filters(): array {
        return array_merge(parent::filters(), [
            'category' => $this->input('category'),
        ]);
    }
}
