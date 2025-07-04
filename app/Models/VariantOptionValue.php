<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class VariantOptionValue extends Model
{
    use HasFactory;

    protected $guarded = [];

    public function productVariants()
    {
        return $this->belongsToMany(ProductVariant::class, 'product_variant_values');
    }

    public function option()
    {
        return $this->belongsTo(VariantOption::class, 'variant_option_id');
    }

public function variantOption()
{
    return $this->belongsTo(VariantOption::class);
}

    
    
}