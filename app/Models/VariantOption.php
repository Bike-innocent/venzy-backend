<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class VariantOption extends Model
{
    use HasFactory;
        protected $guarded = [];
     public function values()
    {
        return $this->hasMany(VariantOptionValue::class);
    }

    

    public function products()
    {
        return $this->belongsToMany(Product::class, 'product_variant_options');
    }
}