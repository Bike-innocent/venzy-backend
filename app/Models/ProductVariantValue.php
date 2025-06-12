<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ProductVariantValue extends Model
{
    use HasFactory;
    protected $guarded = [];

   


    public function productVariant()
    {
        return $this->belongsTo(ProductVariant::class);
    }

    // ProductVariantValue.php
    public function variantOptionValue()
    {
        return $this->belongsTo(VariantOptionValue::class, 'variant_option_value_id');
    }


    
    
}