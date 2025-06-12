<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ProductVariant extends Model
{
    use HasFactory;
    protected $guarded = [];



    public function product()
    {
        return $this->belongsTo(Product::class);
    }

    public function values()
    {
        return $this->hasMany(ProductVariantValue::class);
    }


    public function images()
    {
        return $this->hasMany(ProductImage::class, 'variant_id');
    }


   
    public function variantValues()
    {
        return $this->hasMany(ProductVariantValue::class, 'product_variant_id');
    }


}











    // public function variantValues()
    // {
    //     return $this->hasMany(ProductVariantValue::class);
    // }






















    // protected $appends = ['attributes'];

    // public function getAttributesAttribute()
    // {
    //     return $this->variantValues->map(function ($value) {
    //         return [
    //             'option' => $value->variantOptionValue->variantOption->name ?? null,
    //             'value' => $value->variantOptionValue->value ?? null,
    //         ];
    //     });
    // }


    // public function values()
    // {
    //     return $this->belongsToMany(VariantOptionValue::class, 'product_variant_values');
    // }