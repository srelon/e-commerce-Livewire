<?php

namespace App\Services;

use App\Http\Resources\CartItemResource;
use App\Models\Cart;
use App\Models\CartItem;
use App\Models\Product;
use App\Models\User;
use Illuminate\Support\Facades\DB;

class CartService
{
    public function __construct(protected ProductService $productService) {}

    public function getItems(User $user): array {
        $cart = Cart::where('user_id', $user->id)->first();

        if (! $cart) {
            return [];
        }

        return $cart->items()
            ->where('status', 0)
            ->with(['product.author', 'product.primaryImage', 'product.activeStock'])
            ->get()
            ->map(fn (CartItem $item) => (new CartItemResource($item))->resolve())
            ->all();
    }

    public function addItems(User $user, array $items): array {
        return DB::transaction(function () use ($user, $items) {
            [$conflicts, $resolved] = $this->productService->resolveItems($items);

            $addedItems = [];

            if ($resolved !== []) {
                $cart = Cart::lockForUpdate()->firstOrCreate(['user_id' => $user->id], ['status' => 0]);

                foreach ($resolved as $entry) {
                    $addedItems[] = $this->upsertItem($cart, $entry['product'], $entry['quantity']);
                }
            }

            return ['conflicts' => $conflicts, 'items' => $addedItems];
        });
    }

    protected function upsertItem(Cart $cart, Product $product, int $quantity): CartItem {
        $activeItem = $cart->items()->where('product_id', $product->id)->where('status', 0)->first();

        if ($activeItem) {
            $activeItem->update(['quantity' => $quantity]);

            return $activeItem;
        }

        return $cart->items()->updateOrCreate(
            ['product_id' => $product->id],
            ['quantity' => $quantity, 'status' => 0],
        );
    }

    public function removeItem(User $user, string $slug): void {
        $cart = Cart::where('user_id', $user->id)->first();

        if (! $cart) {
            return;
        }

        $product = Product::where('slug', $slug)->first();

        if (! $product) {
            return;
        }

        $cart->items()->where('product_id', $product->id)->where('status', 0)->update(['status' => 4]);
    }
}
