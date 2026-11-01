<?php

namespace App\Models\Cart;

use App\Models\Catalog\ProductVariant;
use App\Models\Customer\Customer;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasManyThrough;

/**
 * @property int $id
 * @property \Carbon\CarbonImmutable|null $created_at
 * @property \Carbon\CarbonImmutable|null $updated_at
 * @property int|null $customer_id
 * @property string|null $guest_token
 * @property-read \Illuminate\Database\Eloquent\Collection<int, \App\Models\Cart\CartItem> $cartItems
 * @property-read int|null $cart_items_count
 * @property-read Customer|null $customer
 * @property-read \Illuminate\Database\Eloquent\Collection<int, ProductVariant> $productItems
 * @property-read int|null $product_items_count
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Cart newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Cart newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Cart query()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Cart whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Cart whereCustomerId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Cart whereGuestToken($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Cart whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Cart whereUpdatedAt($value)
 * @mixin \Eloquent
 */
class Cart extends Model
{
    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        // Add your fillable attributes here, for example:
        'customer_id',
        'guest_token',



    ];

    /**
     * Get the customer that owns the cart.
     */
    public function customer(): BelongsTo
    {
        return $this->belongsTo(Customer::class);
    }

    /**
     * Get the cart items for the cart.
     */
    public function cartItems(): HasMany
    {
        return $this->hasMany(CartItem::class);
    }

    /**
     * Get the product variants for the cart through cart items.
     */
    public function productItems(): HasManyThrough
    {

        return $this->hasManyThrough(ProductVariant::class, CartItem::class);
    }
}
