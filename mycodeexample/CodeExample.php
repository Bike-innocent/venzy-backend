<?php

// php artisan make:migration create_users_table
// php artisan make:migration create_addresses_table
// php artisan make:migration create_user_accounts_table
// php artisan make:migration create_user_addresses_table
// php artisan make:migration create_product_categories_table
// php artisan make:migration create_brands_table
// php artisan make:migration create_variant_options_table
// php artisan make:migration create_discounts_table
// php artisan make:migration create_suppliers_table
// php artisan make:migration create_product_variant_values_table
// php artisan make:migration create_order_items_table
// php artisan make:migration create_customer_orders_table
// php artisan make:migration create_customer_order_lines_table
// php artisan make:migration create_supplier_orders_table
// php artisan make:migration create_supplier_order_lines_table




// Schema::create('addresses', function (Blueprint $table) {
//     $table->id();
//     $table->string('address_line_1');
//     $table->string('address_line_2')->nullable();
//     $table->string('city');
//     $table->string('state');
//     $table->string('postal_code');
//     $table->string('country');
//     $table->timestamps();
// });






// composer update --ignore-platform-req=ext-fileinfo --ignore-platform-req=ext-exif --with-all-dependencies



// ssh-keygen -t rsa -b 4096 -C "onyemaobichibuikeinnocent.com@gmail.com"
// cat ~/.ssh/id_rsa.pub
// ssh -T git@github.com




Schema::create('product_variants', function (Blueprint $table) {
    $table->id();
    $table->foreignId('product_id')->constrained()->onDelete('cascade');
    $table->string('size')->nullable(); // e.g., S, M, L (mostly for menswear)
    $table->string('volume')->nullable(); // e.g., 50ml, 100ml (for perfume)
    $table->string('color')->nullable();
    $table->timestamps();
});


php artisan make:migration create_product_variants_table









// namespace App\Models;

// use Illuminate\Database\Eloquent\Model;

// class ProductVariant extends Model
// {
//     protected $fillable = ['product_id', 'name', 'sku', 'price', 'stock'];

//     public function product()
//     {
//         return $this->belongsTo(Product::class);
//     }
// }


















php artisan make:factory VariantOptionValueFactory --model=VariantOptionValue