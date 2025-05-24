<?php


namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Str;
use Illuminate\Database\Eloquent\SoftDeletes;

class Product extends Model
{
    use HasFactory, SoftDeletes;
    protected $guarded = [];

    protected static function boot()
    {
        parent::boot();

        // Automatically generate a unique slug when a product is created
        static::creating(function ($product) {
            $product->slug = self::generateUniqueSlug();
        });
    }

    public static function generateUniqueSlug()
    {
        do {
            $slug = Str::random(10);
        } while (self::where('slug', $slug)->exists());

        return $slug;
    }

    // Define the relationships
    public function category()
    {
        return $this->belongsTo(Category::class);
    }



    public function colour()
    {
        return $this->belongsTo(Colour::class);
    }

    public function size()
    {
        return $this->belongsTo(Size::class);
    }



    public function images()
    {
        return $this->hasMany(ProductImage::class);
    }

    public function orderItems()
    {
        return $this->hasMany(OrderItem::class);
    }
}
