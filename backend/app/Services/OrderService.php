<?php

namespace App\Services;

use App\Http\Resources\DeliveryOptionResource;
use App\Http\Resources\OrderContactResource;
use App\Http\Resources\PaymentOptionResource;
use App\Models\DeliveryBranch;
use App\Models\DeliveryService;
use App\Models\Order;
use App\Models\OrderContact;
use App\Models\Payment;
use App\Models\User;
use Illuminate\Support\Facades\DB;

class OrderService
{
    public function __construct(protected ProductService $productService) {}

    public function checkout(array $data, ?User $user): array {
        return DB::transaction(function () use ($data, $user) {
            [$conflicts, $resolved] = $this->productService->resolveItems($data['items']);

            if ($conflicts !== []) {
                return ['conflicts' => $conflicts];
            }

            $delivery = DeliveryService::where('id', $data['delivery']['delivery_id'])->where('status', 1)->firstOrFail();
            $branch = isset($data['delivery']['branch_id'])
                ? $this->resolveBranch($delivery, $data['delivery']['branch_id'])
                : null;
            $payment = Payment::where('key', $data['payment']['method'])->where('status', 1)->firstOrFail();
            $contact = $this->resolveContact($data['contact'], $user, $delivery, $branch);

            $order = Order::create([
                'user_id' => $user?->id,
                'contact_id' => $contact->id,
                'delivery_id' => $delivery->id,
                'delivery_branch_id' => $branch?->id,
                'payment_id' => $payment->id,
                'status' => 0,
            ]);

            foreach ($resolved as $item) {
                $order->items()->create([
                    'product_id' => $item['product']->id,
                    'product_stock_id' => $item['stock']->id,
                    'price' => $item['stock']->price,
                    'quantity' => $item['quantity'],
                    'fact_quantity' => 0,
                    'status' => 0,
                ]);
            }

            return ['order' => $order];
        });
    }

    protected function resolveBranch(DeliveryService $delivery, int $branchId): DeliveryBranch {
        return DeliveryBranch::where('delivery_id', $delivery->id)
            ->where('id', $branchId)
            ->where('status', 1)
            ->firstOrFail();
    }

    protected function resolveContact(array $contact, ?User $user, DeliveryService $delivery, ?DeliveryBranch $branch): OrderContact {
        $existing = OrderContact::where('user_id', $user?->id)
            ->where('first_name', $contact['first_name'])
            ->where('last_name', $contact['last_name'])
            ->where('phone', $contact['phone'])
            ->where('email', $contact['email'])
            ->first();

        $delivery_attributes = [
            'delivery_id' => $delivery->id,
            'delivery_branch_id' => $branch?->id,
            'last_ordered_at' => now(),
        ];

        if ($existing) {
            $existing->update($delivery_attributes);

            return $existing;
        }

        return OrderContact::create([
            'user_id' => $user?->id,
            ...$contact,
            ...$delivery_attributes,
        ]);
    }

    public function getLatestContact(?User $user): ?array {
        if (! $user) {
            return null;
        }

        $contact = OrderContact::where('user_id', $user->id)->latest('last_ordered_at')->first();

        return $contact ? (new OrderContactResource($contact))->resolve() : null;
    }

    public function getDeliveryOptions(): array {
        return CacheService::remember('delivery', 'delivery.options', function () {
            return DeliveryService::query()
                ->where('status', 1)
                ->with(['branches' => fn ($q) => $q->where('status', 1)->orderBy('city')->orderBy('branch')])
                ->get()
                ->map(fn (DeliveryService $delivery) => (new DeliveryOptionResource($delivery))->resolve())
                ->all();
        });
    }

    public function getPaymentOptions(): array {
        return CacheService::remember('payment', 'payment.options', function () {
            return Payment::query()
                ->where('status', 1)
                ->get()
                ->map(fn (Payment $payment) => (new PaymentOptionResource($payment))->resolve())
                ->all();
        });
    }
}
