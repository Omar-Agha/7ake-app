<?php

namespace App\Http\Controllers\Cart;

use App\Core\Cart\CurrentGuestToken;
use App\Http\Controllers\Controller;
use App\Models\Catalog\ProductVariant;
use App\Services\CartService;
use Illuminate\Http\Request;

class CartApiController extends Controller
{
    public function __construct(private CartService $cartService, private CurrentGuestToken $currentGuestToken) {}

    public function getCart(Request $request)
    {

        $cart = $this->cartService->getOrCreateCart($this->currentGuestToken->get());
        // $items = $this->cartService->getCartItems($cart);
        $cart->load('cartItems');
        return response()->json($cart);
    }

    public function addOrUpdateItem()
    {
        $data = request()->validate([
            'variant_id' => 'required|exists:product_variants,id',
            'quantity' => 'required|integer|min:1',

        ]);
        $cart = $this->cartService->getOrCreateCart($this->currentGuestToken->get());
        $variant = ProductVariant::find($data['variant_id']);
        $this->cartService->addOrUpdateCartItem($cart, $variant, $data['quantity']);
        $cart->refresh()->with('cartItems');
        return response()->json($cart);
    }
    public function deleteItemFromCart()
    {

        $data = request()->validate([
            'item_id' => 'required|exists:cart_items,id',
            'variant_id' => 'required|exists:product_variants,id',

        ]);
        $cart = $this->cartService->getOrCreateCart($this->currentGuestToken->get());
        $this->cartService->removeCartItem($cart, $data['variant_id']);

        $cart->refresh()->with('cartItems');
        return response()->json($cart);
    }

    public function clearCart()
    {
        $cart = $this->cartService->getOrCreateCart($this->currentGuestToken->get());
        $this->cartService->clearCart($cart);
        $cart->refresh()->with('cartItems');
        return response()->json($cart);
    }
}
