<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Discount extends Model
{
    use HasFactory;
    protected $guarded = [];
    protected $casts = [
        'starts_at' => 'datetime',
        'ends_at' => 'datetime',
    ];


    // public function isEligibleForCart($cartItems)
    // {
    //     $subtotal = $cartItems->sum(fn($item) => $item->quantity * $item->price);
    //     $totalQuantity = $cartItems->sum('quantity');

    //     // ✅ Usage limit check
    //     if (!is_null($this->usage_limit) && $this->used_count >= $this->usage_limit) {
    //         return false;
    //     }

    //     return match ($this->requirement_type) {
    //         'none' => true,
    //         'min_purchase_amount' => $subtotal >= $this->min_purchase_amount,
    //         'min_quantity' => $totalQuantity >= $this->min_quantity,
    //         default => false,
    //     };
    // }




    public function isEligibleForCart($cartItems, $user = null)
    {
        $subtotal = $cartItems->sum(fn($item) => $item->quantity * $item->price);
        $totalQuantity = $cartItems->sum('quantity');

        // Check global usage limit
        if (!is_null($this->usage_limit) && $this->used_count >= $this->usage_limit) {
            return false;
        }

        // Check per-user limit
        if ($this->once_per_user && $user) {
            $hasUsed = $this->users()->where('user_id', $user->id)->exists();
            if ($hasUsed) return false;
        }

        // Check cart requirements
        return match ($this->requirement_type) {
            'none' => true,
            'min_purchase_amount' => $subtotal >= $this->min_purchase_amount,
            'min_quantity' => $totalQuantity >= $this->min_quantity,
            default => false,
        };
    }


    public function estimatedValue($cartItems)
    {
        $subtotal = $cartItems->sum(fn($item) => $item->quantity * $item->price);
        $shippingPrice = 500;

        return match ($this->discount_type) {
            'order' => $this->value_type === 'percentage'
                ? $subtotal * $this->value / 100
                : $this->value,
            'shipping' => $this->value_type === 'percentage'
                ? $shippingPrice * $this->value / 100
                : ($this->value ?? $shippingPrice),
            default => 0,
        };
    }


    public function products()
    {
        return $this->belongsToMany(Product::class, 'discount_product');
    }

    public function variants()
    {
        return $this->belongsToMany(ProductVariant::class, 'discount_product_variant');
    }

    public function users()
    {
        return $this->belongsToMany(User::class, 'discount_user');
    }

    public function orders()
    {
        return $this->hasMany(Order::class);
    }
}