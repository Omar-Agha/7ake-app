<?php

namespace App\Services;

use App\Core\Customer\CurrentCustomer;
use App\Models\Cart\Cart;
use App\Models\Cart\CartItem;
use App\Models\Catalog\ProductVariant;
use App\Models\Customer\Customer;
use Illuminate\Support\Facades\DB;

/**
 * Service responsible for Cart operations.
 */
class CartService
{

    public function __construct(protected CurrentCustomer $currentCustomer)
    {
        $this->currentCustomer = $currentCustomer;
    }


    public function getOrCreateCart(string $guestToken): Cart
    {
        if ($this->currentCustomer->get()) {
            return $this->getOrCreateCartForCustomer($this->currentCustomer->get(), $guestToken);
        }
        return $this->getOrCreateCartForGuest($guestToken);
    }
    private function getOrCreateCartForCustomer(Customer $customer, string $guestToken): Cart
    {

        $cart = Cart::whereCustomerId($customer->id)->first();
        if ($cart) {
            return $cart;
        }
        $cart = Cart::where('guest_token', $guestToken)->first();
        if ($cart) {
            $cart->customer()->associate($customer);
            $cart->save();
            return $cart;
        }
        return Cart::create(['customer_id' => $customer->id]);
    }


    private function getOrCreateCartForGuest(string $guestToken): Cart
    {
        return Cart::firstOrCreate(['guest_token' => $guestToken]);
    }

    public function getCartItems(Cart $cart): \Illuminate\Database\Eloquent\Collection
    {
        return $cart->cartItems()->with('productVariant')->get();
    }


    public function addOrUpdateCartItem(Cart $cart, ProductVariant $variant, int $quantity): CartItem
    {
        // Use firstOrCreate to respect the unique constraint
        $cartItem = $cart->cartItems()->firstOrCreate(
            ['product_variant_id' => $variant->id],
            [
                'quantity' => $quantity,
            ]
        );
        return $cartItem;
    }


    public function removeCartItem(Cart $cart, int $variant): ?bool
    {
        $cartItem = $cart->cartItems()->where('product_variant_id', $variant)->first();

        if ($cartItem) {
            return $cartItem->delete();
        }

        return null;
    }



    public function clearCart(Cart $cart): void
    {
        $cart->cartItems()->delete();
    }
}
