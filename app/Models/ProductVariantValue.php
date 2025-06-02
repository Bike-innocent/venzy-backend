<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ProductVariantValue extends Model
{
    use HasFactory;

    public function variantOptionValue()
    {
        return $this->belongsTo(VariantOptionValue::class);
    }
    

    public function productVariant()
    {
        return $this->belongsTo(ProductVariant::class);
    }

    
}