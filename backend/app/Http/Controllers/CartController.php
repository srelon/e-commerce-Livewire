<?php

namespace App\Http\Controllers;

use App\Http\Requests\CartItemRequest;
use App\Http\Requests\CartSyncRequest;
use App\Http\Requests\OrderRequest;
use App\Http\Resources\OrderResource;
use App\Services\CartService;
use App\Services\OrderService;
use App\Services\PageService;
use Illuminate\Http\Request;

class CartController extends Controller
{
    public function __construct(
        protected CartService $cartService,
        protected PageService $pageService,
        protected OrderService $orderService,
    ) {}

    public function page(Request $request) {
        return $this->respondWithJson([
            ...$this->pageService->getPage('cart') ?? [],
            'contact' => $this->orderService->getLatestContact($request->user()),
            'delivery_options' => $this->orderService->getDeliveryOptions(),
            'payment_options' => $this->orderService->getPaymentOptions(),
        ]);
    }

    public function store(CartItemRequest $request) {
        $result = $this->cartService->addItems($request->user(), [
            ['slug' => $request->input('slug'), 'quantity' => $request->input('quantity')],
        ]);

        if ($result['conflicts'] !== []) {
            return $this->respondWithError(
                'The available quantity of this item has changed.',
                422,
                extra: ['items' => $result['conflicts']],
            );
        }

        return $this->respondWithJson(['quantity' => $result['items'][0]->quantity]);
    }

    public function sync(CartSyncRequest $request) {
        $result = $this->cartService->addItems($request->user(), $request->input('items'));

        return $this->respondWithJson(['conflicts' => $result['conflicts']]);
    }

    public function destroy(string $slug, Request $request) {
        $this->cartService->removeItem($request->user(), $slug);

        return $this->respondWithJson(['removed' => true]);
    }

    public function checkout(OrderRequest $request) {
        $result = $this->orderService->checkout($request->validated(), $request->user());

        if (isset($result['conflicts'])) {
            return $this->respondWithError(
                'The available quantity of some items has changed.',
                422,
                extra: ['items' => $result['conflicts']],
            );
        }

        return $this->respondWithJson((new OrderResource($result['order']))->resolve());
    }
}
