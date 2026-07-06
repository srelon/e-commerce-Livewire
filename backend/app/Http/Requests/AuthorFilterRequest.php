<?php

namespace App\Http\Requests;

class AuthorFilterRequest extends SortFilterRequest
{
    protected function sortKeys(): array {
        return ['newest', 'books', 'bestseller', 'oldest'];
    }
}
