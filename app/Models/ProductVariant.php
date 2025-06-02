<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class ProductVariant extends Model
{
    use HasFactory, SoftDeletes;
    protected $guarded = [];



    public function product()
    {
        return $this->belongsTo(Product::class);
    }

    public function values()
    {
        return $this->belongsToMany(VariantOptionValue::class, 'product_variant_values');
    }

    public function images()
    {
        return $this->hasMany(ProductImage::class, 'variant_id');
    }

    // App\Models\ProductVariant.php

    public function variantValues()
    {
        return $this->hasMany(ProductVariantValue::class);
    }

   

}