<?php

namespace App\Http\Requests;

use App\Models\Product;
use App\Models\Review;
use Illuminate\Foundation\Http\FormRequest;

class ReviewRequest extends FormRequest
{
    public function authorize(): bool {
        return true;
    }

    public function rules(): array {
        $review_id = $this->route('review');
        $is_reply = $review_id
            ? Review::whereKey($review_id)->whereNotNull('parent_id')->exists()
            : $this->filled('parent_id');

        return [
            'parent_id' => [
                'nullable',
                'integer',
                function (string $attribute, mixed $value, \Closure $fail) {
                    $product = Product::where('slug', $this->route('slug'))->first();

                    if (! $product || ! $product->reviews()->whereNull('parent_id')->where('id', $value)->exists()) {
                        $fail('The selected review to reply to is invalid.');
                    }
                },
            ],
            'replied_to_comment_id' => [
                'nullable',
                'integer',
                function (string $attribute, mixed $value, \Closure $fail) {
                    $target = Review::whereKey($value)->whereNotNull('parent_id')->first();

                    if (! $target || (string) $target->parent_id !== (string) $this->input('parent_id')) {
                        $fail('The selected reply to address is invalid.');
                    }
                },
            ],
            'rating' => [$is_reply ? 'nullable' : 'required', 'integer', 'between:1,5'],
            'body' => [
                'required',
                'string',
                function (string $attribute, mixed $value, \Closure $fail) {
                    $length = mb_strlen(trim(strip_tags($value)));

                    if ($length < 3) {
                        $fail('The body must contain at least 3 characters of text.');
                    }

                    if ($length > 1000) {
                        $fail('The body may not be greater than 1000 characters.');
                    }
                },
            ],
        ];
    }
}
