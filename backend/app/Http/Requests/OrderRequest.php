<?php

namespace App\Http\Requests;

use App\Models\DeliveryService;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class OrderRequest extends FormRequest
{
    public function authorize(): bool {
        return true;
    }

    public function rules(): array {
        $delivery = DeliveryService::find($this->input('delivery.delivery_id'));

        return [
            'contact.first_name' => ['required', 'string', 'max:255'],
            'contact.last_name' => ['required', 'string', 'max:255'],
            'contact.phone' => ['required', 'string', 'max:50'],
            'contact.email' => ['required', 'email'],

            'delivery.delivery_id' => ['required', 'integer', Rule::exists('delivery_services', 'id')->where('status', 1)],
            'delivery.branch_id' => [
                Rule::requiredIf($delivery?->requiresBranch() ?? false),
                'nullable',
                'integer',
                Rule::exists('delivery_branches', 'id')->where('status', 1),
            ],

            'payment.method' => ['required', Rule::exists('payments', 'key')->where('status', 1)],

            'items' => ['required', 'array', 'min:1'],
            'items.*.slug' => ['required', 'string', 'exists:products,slug'],
            'items.*.quantity' => ['required', 'integer', 'min:1'],
        ];
    }
}
